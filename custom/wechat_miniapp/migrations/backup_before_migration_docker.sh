#!/bin/bash

# Docker 环境下的数据库备份脚本
# 使用方法: bash custom/wechat_miniapp/migrations/backup_before_migration_docker.sh

DB_NAME="openemr"
DB_USER="openemr"  # 使用 openemr 用户（root 密码通常是 root，openemr 用户密码是 openemr）
CONTAINER_NAME="development-easy-mysql-1"  # 根据实际情况修改容器名
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)

echo "========================================="
echo "OpenEMR 数据库备份脚本 (Docker)"
echo "========================================="

# 检查 Docker 是否运行
if ! command -v docker &> /dev/null; then
    echo "❌ 错误：找不到 docker 命令"
    exit 1
fi

# 检查容器是否存在
if ! docker ps -a --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "❌ 错误：找不到容器 $CONTAINER_NAME"
    echo ""
    echo "可用的 MySQL 容器："
    docker ps -a --format '{{.Names}}' | grep -i mysql
    echo ""
    echo "请修改脚本中的 CONTAINER_NAME 变量"
    exit 1
fi

# 检查容器是否运行
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "⚠️  容器 $CONTAINER_NAME 未运行，正在启动..."
    docker start $CONTAINER_NAME
    sleep 3
fi

# 创建备份目录
mkdir -p $BACKUP_DIR

echo "请输入数据库密码（输入时不会显示）："
echo "提示：如果使用 openemr 用户，密码通常是：openemr"
echo "     如果使用 root 用户，密码通常是：root"
read -s DB_PASS

if [ -z "$DB_PASS" ]; then
    echo ""
    echo "❌ 错误：密码不能为空"
    exit 1
fi

echo ""
echo "开始备份数据库 $DB_NAME..."

# 检测数据库客户端命令（支持 MySQL 和 MariaDB）
echo "正在检测数据库类型..."
MYSQL_CMD=$(docker exec $CONTAINER_NAME sh -c "command -v mysql || command -v mariadb" 2>/dev/null | head -1)
MYSQLDUMP_CMD=$(docker exec $CONTAINER_NAME sh -c "command -v mysqldump || command -v mariadb-dump" 2>/dev/null | head -1)

if [ -z "$MYSQL_CMD" ]; then
    echo "❌ 错误：容器内找不到 mysql 或 mariadb 命令"
    exit 1
fi

if [ -z "$MYSQLDUMP_CMD" ]; then
    echo "❌ 错误：容器内找不到 mysqldump 或 mariadb-dump 命令"
    exit 1
fi

echo "✅ 检测到数据库客户端：$MYSQL_CMD"
echo "✅ 检测到备份工具：$MYSQLDUMP_CMD"

# 测试数据库连接
echo "正在测试数据库连接..."
# 先尝试连接，捕获错误信息
CONNECTION_TEST=$(docker exec -i $CONTAINER_NAME $MYSQL_CMD -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>&1)
CONNECTION_EXIT_CODE=$?

if [ $CONNECTION_EXIT_CODE -ne 0 ]; then
    echo ""
    echo "❌ 数据库连接失败！"
    echo ""
    echo "错误详情："
    echo "$CONNECTION_TEST" | grep -v "Warning: Using a password"
    echo ""
    echo "可能的原因："
    echo "   1. 数据库密码错误"
    echo "      - 如果使用 openemr 用户，密码通常是：openemr"
    echo "      - 如果使用 root 用户，密码通常是：root"
    echo "   2. 容器名称不正确（当前：$CONTAINER_NAME）"
    echo "   3. 数据库 $DB_NAME 不存在"
    echo ""
    echo "请尝试："
    echo "   1. 运行测试脚本：bash custom/wechat_miniapp/migrations/test_db_connection.sh"
    echo "   2. 检查容器：docker ps | grep mysql"
    echo "   3. 手动测试：docker exec -it $CONTAINER_NAME $MYSQL_CMD -u $DB_USER -p"
    echo "   4. 查看数据库：docker exec -it $CONTAINER_NAME $MYSQL_CMD -u root -proot -e 'SHOW DATABASES;'"
    exit 1
fi

echo "✅ 数据库连接成功"
echo "开始导出数据..."

# 备份整个数据库（使用环境变量方式传递密码）
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_backup_${DATE}.sql"
docker exec -i $CONTAINER_NAME sh -c "MYSQL_PWD='$DB_PASS' $MYSQLDUMP_CMD -u '$DB_USER' '$DB_NAME'" > "$BACKUP_FILE" 2>&1
EXIT_CODE=$?

if [ $EXIT_CODE -eq 0 ] && [ -f "$BACKUP_FILE" ] && [ -s "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo ""
    echo "✅ 备份成功！"
    echo "📁 备份文件：$BACKUP_FILE"
    echo "📊 文件大小：$BACKUP_SIZE"
    echo ""
    echo "⚠️  请妥善保管此备份文件，如需回滚可使用以下命令："
    echo "   docker exec -i $CONTAINER_NAME $MYSQL_CMD -u root -p$DB_PASS $DB_NAME < $BACKUP_FILE"
    echo ""
else
    echo ""
    echo "❌ 备份失败！"
    
    # 显示详细错误信息
    if [ -f "$BACKUP_FILE" ]; then
        echo ""
        echo "错误详情："
        tail -20 "$BACKUP_FILE"
        rm -f "$BACKUP_FILE"
    fi
    
    echo ""
    echo "请检查："
    echo "   1. 数据库密码是否正确（默认可能是：openemr）"
    echo "   2. 容器名称是否正确"
    echo "   3. 数据库 $DB_NAME 是否存在"
    exit 1
fi

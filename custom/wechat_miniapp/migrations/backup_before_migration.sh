#!/bin/bash

# 数据库迁移前备份脚本
# 使用方法: bash custom/wechat_miniapp/migrations/backup_before_migration.sh

DB_NAME="openemr"
DB_USER="root"
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)

echo "========================================="
echo "OpenEMR 数据库备份脚本"
echo "========================================="

# 检查 mysqldump 命令是否存在
if ! command -v mysqldump &> /dev/null; then
    echo "❌ 错误：找不到 mysqldump 命令"
    echo ""
    echo "请确保 MySQL 客户端已安装并在 PATH 中"
    echo "如果使用 Docker，请使用："
    echo "  docker exec -i openemr_mysql mysqldump -u root -p$DB_PASS $DB_NAME > backup.sql"
    exit 1
fi

# 创建备份目录
mkdir -p $BACKUP_DIR

# 检查备份目录是否可写
if [ ! -w "$BACKUP_DIR" ]; then
    echo "❌ 错误：备份目录 $BACKUP_DIR 不可写"
    exit 1
fi

echo "请输入数据库密码（输入时不会显示）："
read -s DB_PASS

if [ -z "$DB_PASS" ]; then
    echo ""
    echo "❌ 错误：密码不能为空"
    exit 1
fi

echo ""
echo "开始备份数据库 $DB_NAME..."

# 测试数据库连接
echo "正在测试数据库连接..."
if ! mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
    echo ""
    echo "❌ 数据库连接失败！"
    echo ""
    echo "可能的原因："
    echo "   1. 数据库用户名或密码错误"
    echo "   2. 数据库服务未运行"
    echo "   3. 数据库 $DB_NAME 不存在"
    echo ""
    echo "如果使用 Docker，请检查："
    echo "   docker ps | grep mysql"
    echo "   docker exec -it openemr_mysql mysql -u root -p"
    exit 1
fi

echo "✅ 数据库连接成功"
echo "开始导出数据..."

# 备份整个数据库（使用更安全的方式传递密码）
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_backup_${DATE}.sql"
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>&1
EXIT_CODE=$?

if [ $EXIT_CODE -eq 0 ] && [ -f "$BACKUP_FILE" ] && [ -s "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo ""
    echo "✅ 备份成功！"
    echo "📁 备份文件：$BACKUP_FILE"
    echo "📊 文件大小：$BACKUP_SIZE"
    echo ""
    echo "⚠️  请妥善保管此备份文件，如需回滚可使用以下命令："
    echo "   mysql -u root -p openemr < $BACKUP_FILE"
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
    echo "   1. 数据库用户名和密码是否正确"
    echo "   2. 是否有备份目录的写入权限"
    echo "   3. 数据库服务是否正常运行"
    echo "   4. 数据库 $DB_NAME 是否存在"
    echo ""
    echo "如果使用 Docker，可以尝试："
    echo "   docker exec -i openemr_mysql mysqldump -u root -p$DB_PASS $DB_NAME > $BACKUP_FILE"
    exit 1
fi

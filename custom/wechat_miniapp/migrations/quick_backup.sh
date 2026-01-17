#!/bin/bash

# 快速备份脚本（使用默认配置）
# 使用方法: bash custom/wechat_miniapp/migrations/quick_backup.sh

CONTAINER_NAME="development-easy-mysql-1"
DB_NAME="openemr"
DB_USER="openemr"
DB_PASS="openemr"  # 默认密码
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)

echo "========================================="
echo "快速备份脚本（使用默认配置）"
echo "========================================="

# 检测命令
MYSQLDUMP_CMD=$(docker exec $CONTAINER_NAME sh -c "command -v mysqldump || command -v mariadb-dump" 2>/dev/null | head -1)

if [ -z "$MYSQLDUMP_CMD" ]; then
    echo "❌ 错误：找不到备份工具"
    exit 1
fi

# 创建备份目录
mkdir -p $BACKUP_DIR

echo "开始备份..."
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_backup_${DATE}.sql"

# 使用环境变量方式传递密码
docker exec -i $CONTAINER_NAME sh -c "MYSQL_PWD='$DB_PASS' $MYSQLDUMP_CMD -u '$DB_USER' '$DB_NAME'" > "$BACKUP_FILE" 2>&1
EXIT_CODE=$?

if [ $EXIT_CODE -eq 0 ] && [ -f "$BACKUP_FILE" ] && [ -s "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo ""
    echo "✅ 备份成功！"
    echo "📁 备份文件：$BACKUP_FILE"
    echo "📊 文件大小：$BACKUP_SIZE"
else
    echo ""
    echo "❌ 备份失败！"
    if [ -f "$BACKUP_FILE" ]; then
        echo "错误详情："
        cat "$BACKUP_FILE"
        rm -f "$BACKUP_FILE"
    fi
    echo ""
    echo "请尝试手动备份："
    echo "  docker exec -i $CONTAINER_NAME mariadb-dump -u openemr -popenemr openemr > backup.sql"
    exit 1
fi

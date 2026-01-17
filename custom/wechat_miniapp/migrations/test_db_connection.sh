#!/bin/bash

# 快速测试数据库连接的脚本
# 使用方法: bash custom/wechat_miniapp/migrations/test_db_connection.sh

CONTAINER_NAME="development-easy-mysql-1"

echo "========================================="
echo "测试数据库连接"
echo "========================================="

# 检测数据库客户端命令
MYSQL_CMD=$(docker exec $CONTAINER_NAME sh -c "command -v mysql || command -v mariadb" 2>/dev/null | head -1)

if [ -z "$MYSQL_CMD" ]; then
    echo "❌ 错误：容器内找不到 mysql 或 mariadb 命令"
    exit 1
fi

echo "✅ 检测到数据库客户端：$MYSQL_CMD"
echo ""

# 测试 root 用户（密码：root）
echo "测试 1: root 用户，密码 root"
if docker exec -i $CONTAINER_NAME $MYSQL_CMD -u root -proot -e "SELECT 1;" 2>/dev/null | grep -q "1"; then
    echo "✅ root/root 连接成功"
    echo "   可用命令：docker exec -i $CONTAINER_NAME $MYSQL_CMD -u root -proot"
else
    echo "❌ root/root 连接失败"
fi

echo ""

# 测试 openemr 用户（密码：openemr）
echo "测试 2: openemr 用户，密码 openemr"
if docker exec -i $CONTAINER_NAME $MYSQL_CMD -u openemr -popenemr -e "SELECT 1;" 2>/dev/null | grep -q "1"; then
    echo "✅ openemr/openemr 连接成功"
    echo "   可用命令：docker exec -i $CONTAINER_NAME $MYSQL_CMD -u openemr -popenemr"
else
    echo "❌ openemr/openemr 连接失败"
fi

echo ""

# 列出所有数据库
echo "查看所有数据库（使用 root/root）："
docker exec -i $CONTAINER_NAME $MYSQL_CMD -u root -proot -e "SHOW DATABASES;" 2>/dev/null || echo "无法连接"

echo ""
echo "========================================="
echo "如果以上测试都失败，请检查："
echo "   1. 容器是否运行：docker ps | grep $CONTAINER_NAME"
echo "   2. 容器日志：docker logs $CONTAINER_NAME | tail -20"
echo "========================================="

#!/bin/bash

# 专门用于调试 new_search_popup.php 的脚本
# 这个脚本会：
# 1. 启用 PHP 错误显示
# 2. 实时查看相关日志

COMPOSE_FILE="docker/development-easy/docker-compose.yml"
CONTAINER_NAME="development-easy-openemr-1"
PHP_FILE="interface/mobile/new/new_search_popup.php"

echo "=== 调试 new_search_popup.php ==="
echo ""
echo "1. 检查文件是否存在..."
if [ -f "$PHP_FILE" ]; then
    echo "✓ 文件存在: $PHP_FILE"
else
    echo "✗ 文件不存在: $PHP_FILE"
    exit 1
fi

echo ""
echo "2. 检查代码挂载..."
docker exec $CONTAINER_NAME ls -la /var/www/localhost/htdocs/openemr/$PHP_FILE > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✓ 代码已正确挂载到容器"
else
    echo "✗ 代码未正确挂载到容器"
    exit 1
fi

echo ""
echo "3. 启用 PHP 错误显示..."
# 在文件开头添加错误显示（如果还没有）
if ! grep -q "error_reporting(E_ALL)" "$PHP_FILE"; then
    # 在第17行之后添加错误显示
    sed -i.bak '17a\
error_reporting(E_ALL);\
ini_set("display_errors", 1);\
ini_set("log_errors", 1);\
ini_set("error_log", "/var/log/php_errors.log");
' "$PHP_FILE"
    echo "✓ 已启用 PHP 错误显示"
else
    echo "✓ PHP 错误显示已启用"
fi

echo ""
echo "4. 开始实时查看日志..."
echo "   现在请在浏览器中测试新增病人功能"
echo "   按 Ctrl+C 退出日志查看"
echo ""

# 实时查看容器日志，过滤错误和警告
docker compose -f $COMPOSE_FILE logs -f openemr 2>&1 | grep -i -E "(error|warning|fatal|exception|new_search_popup)" --color=always

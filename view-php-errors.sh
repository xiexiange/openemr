#!/bin/bash

# 查看 PHP 错误日志
# 这个脚本会实时查看 OpenEMR 容器内的 PHP 错误日志

COMPOSE_FILE="docker/development-easy/docker-compose.yml"
CONTAINER_NAME="development-easy-openemr-1"

echo "正在查看 PHP 错误日志..."
echo "按 Ctrl+C 退出"
echo ""

# 查看容器内的 PHP 错误日志
# 首先尝试常见的 PHP 错误日志位置
docker exec -it $CONTAINER_NAME sh -c "
  if [ -f /var/log/php_errors.log ]; then
    tail -f /var/log/php_errors.log
  elif [ -f /var/log/apache2/error.log ]; then
    tail -f /var/log/apache2/error.log
  elif [ -f /var/log/nginx/error.log ]; then
    tail -f /var/log/nginx/error.log
  else
    echo '未找到 PHP 错误日志文件'
    echo '尝试查看容器日志...'
    exit 1
  fi
" || docker compose -f $COMPOSE_FILE logs -f openemr | grep -i error

#!/bin/bash

# OpenEMR 实时日志查看脚本
# 用法: ./view-logs.sh [service_name]
# 例如: ./view-logs.sh openemr

SERVICE_NAME=${1:-openemr}
COMPOSE_FILE="docker/development-easy/docker-compose.yml"

echo "正在查看 $SERVICE_NAME 服务的实时日志..."
echo "按 Ctrl+C 退出"
echo ""

# 查看容器日志
docker compose -f $COMPOSE_FILE logs -f $SERVICE_NAME

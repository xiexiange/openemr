#!/bin/bash

# Docker 环境彻底清理脚本
# 警告：此脚本会删除所有 Docker 容器、卷和镜像数据

set -e

echo "=========================================="
echo "  Docker 环境彻底清理脚本"
echo "=========================================="
echo ""
echo "⚠️  警告：此操作会删除所有 Docker 数据！"
echo "   包括："
echo "   - 所有容器（运行中和已停止）"
echo "   - 所有数据卷（数据库、站点数据等）"
echo "   - 所有未使用的镜像"
echo "   - 所有构建缓存"
echo ""
read -p "确认继续？(yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "操作已取消"
    exit 0
fi

echo ""
echo "开始清理..."

# 获取项目目录
PROJECT_DIR="/Users/xian/Documents/创业app/doctor/openemr"
cd "$PROJECT_DIR"

# 1. 停止所有 Compose 服务
echo ""
echo "1. 停止所有 Docker Compose 服务..."
if [ -d "docker/development-easy" ]; then
    cd docker/development-easy
    docker compose down 2>/dev/null || true
    cd "$PROJECT_DIR"
fi

if [ -d "docker/production" ]; then
    cd docker/production
    docker compose down 2>/dev/null || true
    cd "$PROJECT_DIR"
fi

if [ -d "docker/development-easy-light" ]; then
    cd docker/development-easy-light
    docker compose down 2>/dev/null || true
    cd "$PROJECT_DIR"
fi

# 2. 停止所有相关容器
echo ""
echo "2. 停止所有相关容器..."
docker stop $(docker ps -aq --filter "name=openemr") 2>/dev/null || true
docker stop $(docker ps -aq --filter "name=mysql") 2>/dev/null || true
docker stop $(docker ps -aq --filter "name=phpmyadmin") 2>/dev/null || true

# 3. 删除所有容器
echo ""
echo "3. 删除所有容器..."
docker container prune -f

# 4. 删除所有数据卷
echo ""
echo "4. 删除所有数据卷..."
echo "   删除 OpenEMR 相关卷..."
docker volume rm $(docker volume ls -q --filter "name=openemr") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=database") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=site") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=log") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=asset") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=theme") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=node") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=vendor") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=ccda") 2>/dev/null || true
docker volume rm $(docker volume ls -q --filter "name=couchdb") 2>/dev/null || true

echo "   删除所有未使用的卷..."
docker volume prune -f

# 5. 删除镜像（可选）
echo ""
read -p "5. 是否删除所有未使用的镜像？(yes/no，默认no): " delete_images
if [ "$delete_images" = "yes" ]; then
    echo "   删除未使用的镜像..."
    docker image prune -a -f
else
    echo "   跳过镜像删除"
fi

# 6. 清理系统
echo ""
echo "6. 清理系统资源（网络、构建缓存等）..."
docker system prune -f

# 7. 显示清理结果
echo ""
echo "=========================================="
echo "  清理完成！"
echo "=========================================="
echo ""
echo "当前 Docker 状态："
echo ""
echo "--- 容器 ---"
docker ps -a 2>/dev/null || echo "  (无容器)"
echo ""
echo "--- 数据卷 ---"
docker volume ls 2>/dev/null || echo "  (无数据卷)"
echo ""
echo "--- 镜像（前5个）---"
docker images | head -6 2>/dev/null || echo "  (无镜像)"
echo ""
echo "--- 磁盘使用 ---"
docker system df 2>/dev/null || echo "  (无法获取信息)"
echo ""
echo "✅ 清理完成！现在可以重新启动 Docker 服务了。"
echo ""
echo "重新启动命令："
echo "  cd docker/development-easy"
echo "  docker compose up -d"

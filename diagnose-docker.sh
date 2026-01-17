#!/bin/bash

# Docker 环境快速诊断脚本

echo "=========================================="
echo "  Docker 环境诊断"
echo "=========================================="
echo ""

# 1. 检查 Docker 命令是否可用
echo "1. 检查 Docker 命令..."
if command -v docker &> /dev/null; then
    echo "   ✅ Docker 命令可用"
    docker --version
else
    echo "   ❌ Docker 命令不可用"
    echo "   请确保 Docker Desktop 已安装并启动"
    echo "   启动命令: open -a Docker"
    exit 1
fi

# 2. 检查 Docker 守护进程
echo ""
echo "2. 检查 Docker 守护进程..."
if docker info &> /dev/null; then
    echo "   ✅ Docker 守护进程运行正常"
else
    echo "   ❌ Docker 守护进程未运行"
    echo "   请启动 Docker Desktop: open -a Docker"
    exit 1
fi

# 3. 检查容器状态
echo ""
echo "3. 检查容器状态..."
PROJECT_DIR="/Users/xian/Documents/创业app/doctor/openemr"
cd "$PROJECT_DIR" 2>/dev/null || exit 1

containers=$(docker ps -a --format "{{.Names}}" 2>/dev/null | grep -E "openemr|mysql" || true)
if [ -z "$containers" ]; then
    echo "   ℹ️  没有找到 OpenEMR 相关容器"
else
    echo "   找到以下容器："
    docker ps -a --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "NAMES|openemr|mysql"
fi

# 4. 检查 Compose 服务
echo ""
echo "4. 检查 Docker Compose 服务..."
if [ -d "docker/development-easy" ]; then
    cd docker/development-easy
    echo "   开发环境 (development-easy):"
    docker compose ps 2>/dev/null || echo "     无法获取服务状态"
    cd "$PROJECT_DIR"
fi

if [ -d "docker/production" ]; then
    cd docker/production
    echo "   生产环境 (production):"
    docker compose ps 2>/dev/null || echo "     无法获取服务状态"
    cd "$PROJECT_DIR"
fi

# 5. 检查端口占用
echo ""
echo "5. 检查端口占用..."
ports=(8300 9300 8320 8310)
for port in "${ports[@]}"; do
    process=$(lsof -ti:$port 2>/dev/null || true)
    if [ -n "$process" ]; then
        echo "   ⚠️  端口 $port 被占用 (PID: $process)"
        ps -p $process -o comm= 2>/dev/null || true
    else
        echo "   ✅ 端口 $port 可用"
    fi
done

# 6. 检查磁盘空间
echo ""
echo "6. 检查 Docker 磁盘使用..."
docker system df 2>/dev/null || echo "   无法获取磁盘使用信息"

# 7. 检查数据卷
echo ""
echo "7. 检查数据卷..."
volumes=$(docker volume ls --format "{{.Name}}" 2>/dev/null | grep -E "openemr|database|site|log" || true)
if [ -z "$volumes" ]; then
    echo "   ℹ️  没有找到相关数据卷"
else
    echo "   找到以下数据卷："
    docker volume ls | grep -E "DRIVER|openemr|database|site|log"
fi

# 8. 检查最近的错误日志
echo ""
echo "8. 检查最近的错误日志..."
if [ -d "docker/development-easy" ]; then
    cd docker/development-easy
    echo "   OpenEMR 服务日志（最后10行）:"
    docker compose logs openemr --tail=10 2>/dev/null | grep -i error || echo "     无错误日志"
    echo ""
    echo "   MySQL 服务日志（最后10行）:"
    docker compose logs mysql --tail=10 2>/dev/null | grep -i error || echo "     无错误日志"
    cd "$PROJECT_DIR"
fi

# 9. 总结
echo ""
echo "=========================================="
echo "  诊断完成"
echo "=========================================="
echo ""
echo "如果发现问题，可以："
echo "  1. 运行清理脚本: ./cleanup-docker.sh"
echo "  2. 查看详细指南: cat DOCKER_TROUBLESHOOTING_GUIDE.md"
echo "  3. 重启 Docker Desktop: 完全退出后重新启动"
echo ""

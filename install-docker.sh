#!/bin/bash

echo "=========================================="
echo "  Docker Desktop 安装助手"
echo "=========================================="
echo ""

# 检查是否已安装
if [ -d "/Applications/Docker.app" ]; then
    echo "✅ Docker Desktop 已安装"
    echo "   位置: /Applications/Docker.app"
    echo ""
    read -p "是否启动 Docker Desktop? (yes/no): " start_docker
    if [ "$start_docker" = "yes" ]; then
        open -a Docker
        echo "正在启动 Docker Desktop，请等待..."
        sleep 5
        echo "等待 Docker 完全启动（约30-60秒）..."
    fi
    exit 0
fi

echo "❌ Docker Desktop 未安装"
echo ""
echo "请选择安装方式："
echo "1. 手动下载安装（推荐）"
echo "2. 使用 Homebrew 安装"
echo ""
read -p "请选择 (1/2): " choice

case $choice in
    1)
        echo ""
        echo "📥 手动安装步骤："
        echo "1. 打开浏览器访问: https://www.docker.com/products/docker-desktop/"
        echo "2. 下载 Docker Desktop for Mac (Apple Silicon)"
        echo "3. 打开下载的 .dmg 文件"
        echo "4. 将 Docker 拖拽到 Applications 文件夹"
        echo "5. 打开 Applications，双击 Docker 启动"
        echo ""
        echo "是否现在打开下载页面？"
        read -p "(yes/no): " open_browser
        if [ "$open_browser" = "yes" ]; then
            open "https://www.docker.com/products/docker-desktop/"
        fi
        ;;
    2)
        echo ""
        echo "检查 Homebrew..."
        if ! command -v brew &> /dev/null; then
            echo "❌ Homebrew 未安装"
            echo "   正在安装 Homebrew..."
            /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
        fi
        
        echo "✅ Homebrew 已安装"
        echo ""
        echo "正在使用 Homebrew 安装 Docker Desktop..."
        brew install --cask docker
        
        echo ""
        echo "✅ 安装完成！"
        echo "   正在启动 Docker Desktop..."
        open -a Docker
        ;;
    *)
        echo "无效选择"
        exit 1
        ;;
esac

echo ""
echo "安装完成后，请运行以下命令验证："
echo "  docker --version"
echo "  ./diagnose-docker.sh"

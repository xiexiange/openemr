#!/bin/bash

# Docker "too many open files" 永久修复 - 一键执行脚本
# 此脚本会执行所有必要的配置步骤

set -e

echo "=========================================="
echo "  Docker 文件描述符限制 - 永久修复"
echo "=========================================="
echo ""
echo "此脚本将："
echo "  1. 创建系统级配置文件（需要管理员权限）"
echo "  2. 创建用户级配置文件"
echo "  3. 更新 shell 配置"
echo "  4. 立即应用新限制"
echo ""
read -p "按 Enter 继续，或 Ctrl+C 取消..."

# 显示当前限制
echo ""
echo "当前系统限制："
echo "  ulimit -n: $(ulimit -n)"
echo "  launchctl limit maxfiles: $(launchctl limit maxfiles)"
echo ""

# 步骤1: 创建系统级配置文件
echo "步骤1: 创建系统级限制配置文件..."
sudo tee /Library/LaunchDaemons/limit.maxfiles.plist > /dev/null <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
  <dict>
    <key>Label</key>
    <string>limit.maxfiles</string>
    <key>ProgramArguments</key>
    <array>
      <string>launchctl</string>
      <string>limit</string>
      <string>maxfiles</string>
      <string>65536</string>
      <string>200000</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>ServiceIPC</key>
    <false/>
  </dict>
</plist>
EOF

echo "✅ 系统配置文件已创建: /Library/LaunchDaemons/limit.maxfiles.plist"

# 步骤2: 加载系统配置
echo ""
echo "步骤2: 加载系统限制配置..."
sudo launchctl load -w /Library/LaunchDaemons/limit.maxfiles.plist 2>/dev/null || \
sudo launchctl bootstrap system /Library/LaunchDaemons/limit.maxfiles.plist 2>/dev/null || true

# 步骤3: 设置当前系统限制
echo ""
echo "步骤3: 设置当前系统限制..."
sudo launchctl limit maxfiles 65536 200000

# 步骤4: 创建用户级配置
echo ""
echo "步骤4: 创建用户级配置..."
mkdir -p ~/Library/LaunchAgents

cat > ~/Library/LaunchAgents/limit.maxfiles.plist <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
  <dict>
    <key>Label</key>
    <string>limit.maxfiles</string>
    <key>ProgramArguments</key>
    <array>
      <string>launchctl</string>
      <string>limit</string>
      <string>maxfiles</string>
      <string>65536</string>
      <string>200000</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>ServiceIPC</key>
    <false/>
  </dict>
</plist>
EOF

echo "✅ 用户配置文件已创建: ~/Library/LaunchAgents/limit.maxfiles.plist"

# 步骤5: 加载用户配置
launchctl load -w ~/Library/LaunchAgents/limit.maxfiles.plist 2>/dev/null || \
launchctl bootstrap gui/$(id -u) ~/Library/LaunchAgents/limit.maxfiles.plist 2>/dev/null || true

# 步骤6: 更新 shell 配置
echo ""
echo "步骤6: 更新 shell 配置..."

SHELL_CONFIG=""
if [ -f "$HOME/.zshrc" ]; then
    SHELL_CONFIG="$HOME/.zshrc"
elif [ -f "$HOME/.bash_profile" ]; then
    SHELL_CONFIG="$HOME/.bash_profile"
elif [ -f "$HOME/.bashrc" ]; then
    SHELL_CONFIG="$HOME/.bashrc"
fi

if [ -n "$SHELL_CONFIG" ]; then
    if ! grep -q "ulimit -n 65536" "$SHELL_CONFIG"; then
        echo "" >> "$SHELL_CONFIG"
        echo "# Increase file descriptor limit for Docker" >> "$SHELL_CONFIG"
        echo "ulimit -n 65536" >> "$SHELL_CONFIG"
        echo "✅ 已添加到 $SHELL_CONFIG"
    else
        echo "ℹ️  $SHELL_CONFIG 中已存在配置"
    fi
else
    echo "⚠️  未找到 shell 配置文件，请手动添加: ulimit -n 65536"
fi

# 步骤7: 应用当前会话限制
echo ""
echo "步骤7: 应用当前会话限制..."
ulimit -n 65536

# 显示结果
echo ""
echo "=========================================="
echo "  修复完成！"
echo "=========================================="
echo ""
echo "新的限制："
echo "  ulimit -n: $(ulimit -n)"
echo "  launchctl limit maxfiles: $(launchctl limit maxfiles)"
echo ""
echo "✅ 配置已永久保存，重启后仍然有效"
echo ""
echo "⚠️  下一步操作："
echo "  1. 重启 Docker Desktop（必须）"
echo "  2. 验证修复是否成功"
echo ""
echo "重启 Docker Desktop 命令："
echo "  osascript -e 'quit app \"Docker\"' && sleep 3 && open -a Docker"
echo ""
read -p "是否现在重启 Docker Desktop? (yes/no): " restart_docker

if [ "$restart_docker" = "yes" ]; then
    echo ""
    echo "正在重启 Docker Desktop..."
    osascript -e 'quit app "Docker"' 2>/dev/null || true
    sleep 3
    open -a Docker
    echo "✅ Docker Desktop 正在启动，请等待约30-60秒..."
    echo ""
    echo "启动后，运行以下命令验证："
    echo "  docker info"
fi

echo ""
echo "修复完成！"

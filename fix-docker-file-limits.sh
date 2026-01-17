#!/bin/bash

# Docker "too many open files" 问题修复脚本
# 此脚本会提高 macOS 系统的文件描述符限制

set -e

echo "=========================================="
echo "  Docker 文件描述符限制修复"
echo "=========================================="
echo ""

# 检查是否为 root 或使用 sudo
if [ "$EUID" -ne 0 ]; then
    echo "⚠️  此脚本需要管理员权限"
    echo "   将使用 sudo 执行部分命令"
    echo ""
fi

# 显示当前限制
echo "当前系统限制："
echo "  ulimit -n: $(ulimit -n)"
echo "  launchctl limit maxfiles: $(launchctl limit maxfiles)"
echo "  kern.maxfiles: $(sysctl -n kern.maxfiles)"
echo "  kern.maxfilesperproc: $(sysctl -n kern.maxfilesperproc)"
echo ""

# 创建 plist 文件来设置系统级限制
PLIST_FILE="/Library/LaunchDaemons/limit.maxfiles.plist"

echo "步骤1: 创建系统级限制配置文件..."

sudo tee "$PLIST_FILE" > /dev/null <<EOF
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

echo "✅ 配置文件已创建: $PLIST_FILE"
echo ""

# 加载配置
echo "步骤2: 加载系统限制配置..."
sudo launchctl load -w "$PLIST_FILE" 2>/dev/null || sudo launchctl bootstrap system "$PLIST_FILE" 2>/dev/null || true

# 设置当前会话的限制
echo "步骤3: 设置当前会话限制..."
sudo launchctl limit maxfiles 65536 200000

# 创建用户级配置（用于新终端会话）
echo "步骤4: 创建用户级配置..."
USER_PLIST_FILE="$HOME/Library/LaunchAgents/limit.maxfiles.plist"
mkdir -p "$HOME/Library/LaunchAgents"

cat > "$USER_PLIST_FILE" <<EOF
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

echo "✅ 用户配置文件已创建: $USER_PLIST_FILE"

# 加载用户配置
launchctl load -w "$USER_PLIST_FILE" 2>/dev/null || launchctl bootstrap gui/$(id -u) "$USER_PLIST_FILE" 2>/dev/null || true

# 更新 shell 配置文件
echo ""
echo "步骤5: 更新 shell 配置..."

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

# 应用当前会话的限制
ulimit -n 65536

echo ""
echo "=========================================="
echo "  修复完成！"
echo "=========================================="
echo ""
echo "新的限制："
echo "  ulimit -n: $(ulimit -n)"
echo "  launchctl limit maxfiles: $(launchctl limit maxfiles)"
echo ""
echo "⚠️  重要提示："
echo "  1. 请重启 Docker Desktop 使更改生效"
echo "  2. 如果问题仍然存在，请重启 Mac"
echo "  3. 新打开的终端窗口会自动应用新限制"
echo ""
echo "重启 Docker Desktop 命令："
echo "  osascript -e 'quit app \"Docker\"' && sleep 2 && open -a Docker"
echo ""

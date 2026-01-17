# ⚡ 立即执行永久修复

## ✅ 已完成的部分（无需管理员权限）

我已经为你完成了以下配置：

1. ✅ 创建了用户级配置文件：`~/Library/LaunchAgents/limit.maxfiles.plist`
2. ✅ 更新了 Shell 配置：`~/.zshrc`（添加了 `ulimit -n 65536`）
3. ✅ 设置了当前会话限制：`ulimit -n 65536`

---

## 🔐 需要你手动执行的部分（需要管理员权限）

**请在终端中复制粘贴以下命令并执行：**

```bash
# 1. 创建系统级配置文件（需要输入密码）
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

# 2. 加载系统配置
sudo launchctl load -w /Library/LaunchDaemons/limit.maxfiles.plist 2>/dev/null || sudo launchctl bootstrap system /Library/LaunchDaemons/limit.maxfiles.plist

# 3. 设置当前系统限制
sudo launchctl limit maxfiles 65536 200000
```

**执行时会要求输入你的 Mac 登录密码（输入时不会显示字符，这是正常的）。**

---

## 🔄 重启 Docker Desktop

执行完上述命令后，重启 Docker Desktop：

```bash
# 完全退出 Docker Desktop
osascript -e 'quit app "Docker"'

# 等待几秒
sleep 3

# 重新启动
open -a Docker

# 等待 Docker 完全启动（约30-60秒）
```

---

## ✅ 验证修复

重启 Docker Desktop 后，运行以下命令验证：

```bash
# 检查限制（应该显示 65536）
ulimit -n

# 检查系统限制（应该显示 65536 200000）
launchctl limit maxfiles

# 验证 Docker 正常工作
docker info
```

---

## 📋 快速执行（一键复制）

如果你想一次性执行所有命令，复制以下内容到终端：

```bash
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
sudo launchctl load -w /Library/LaunchDaemons/limit.maxfiles.plist 2>/dev/null || sudo launchctl bootstrap system /Library/LaunchDaemons/limit.maxfiles.plist
sudo launchctl limit maxfiles 65536 200000
osascript -e 'quit app "Docker"' && sleep 3 && open -a Docker
```

---

## 🎉 完成！

执行完上述命令后，Docker 的 "too many open files" 问题应该就永久解决了！

如果仍有问题，可以：
1. 重启 Mac（确保系统级配置完全生效）
2. 查看详细指南：`PERMANENT_FIX_STEPS.md`

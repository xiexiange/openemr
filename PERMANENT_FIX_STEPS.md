# Docker "too many open files" 永久修复步骤

## 🎯 永久修复方案

此修复会：
- ✅ 创建系统级配置文件（重启后仍然有效）
- ✅ 创建用户级配置文件（新终端自动应用）
- ✅ 更新 shell 配置（每次打开终端自动设置）
- ✅ 立即应用新限制

---

## 📋 执行步骤

### 方法1: 使用修复脚本（推荐）

**在终端中执行以下命令：**

```bash
cd /Users/xian/Documents/创业app/doctor/openemr
./fix-docker-file-limits.sh
```

脚本会提示输入管理员密码，输入后会自动完成所有配置。

---

### 方法2: 手动执行（如果脚本无法运行）

**复制以下命令到终端，逐行执行：**

```bash
# 1. 创建系统级配置文件
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

# 4. 创建用户级配置
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

# 5. 加载用户配置
launchctl load -w ~/Library/LaunchAgents/limit.maxfiles.plist 2>/dev/null || launchctl bootstrap gui/$(id -u) ~/Library/LaunchAgents/limit.maxfiles.plist

# 6. 更新 shell 配置（zsh）
if [ -f ~/.zshrc ]; then
    if ! grep -q "ulimit -n 65536" ~/.zshrc; then
        echo "" >> ~/.zshrc
        echo "# Increase file descriptor limit for Docker" >> ~/.zshrc
        echo "ulimit -n 65536" >> ~/.zshrc
    fi
fi

# 7. 应用当前会话限制
ulimit -n 65536

# 8. 验证配置
echo "当前限制："
ulimit -n
launchctl limit maxfiles
```

---

## ✅ 修复后必须执行的操作

### 1. 重启 Docker Desktop

```bash
# 完全退出 Docker Desktop
osascript -e 'quit app "Docker"'

# 等待几秒
sleep 3

# 重新启动
open -a Docker

# 等待 Docker 完全启动（约30-60秒）
```

### 2. 验证修复

```bash
# 检查限制（应该显示 65536）
ulimit -n

# 检查系统限制（应该显示 65536 200000）
launchctl limit maxfiles

# 验证 Docker 正常工作
docker info
```

---

## 🔄 如果问题仍然存在

### 选项1: 重启 Mac（推荐）

系统级配置需要重启才能完全生效：

```bash
sudo reboot
```

重启后，所有配置会自动加载。

### 选项2: 手动重新加载配置

```bash
# 重新加载系统配置
sudo launchctl load -w /Library/LaunchDaemons/limit.maxfiles.plist

# 重新加载用户配置
launchctl load -w ~/Library/LaunchAgents/limit.maxfiles.plist

# 重新设置限制
sudo launchctl limit maxfiles 65536 200000
ulimit -n 65536
```

---

## 📊 验证永久修复成功

### 检查1: 当前会话限制

```bash
ulimit -n
# 应该显示: 65536
```

### 检查2: 系统限制

```bash
launchctl limit maxfiles
# 应该显示: maxfiles    65536           200000
```

### 检查3: 新终端窗口自动应用

1. 打开新的终端窗口
2. 运行 `ulimit -n`
3. 应该自动显示 `65536`

### 检查4: 重启后仍然有效

1. 重启 Mac
2. 打开终端
3. 运行 `launchctl limit maxfiles`
4. 应该显示 `65536 200000`

---

## 🛠️ 故障排除

### 问题1: sudo 命令要求密码

**解决方案**: 这是正常的，输入你的 Mac 登录密码即可（输入时不会显示字符）。

### 问题2: launchctl load 失败

**解决方案**: 在新版 macOS 上，使用 `bootstrap` 命令：

```bash
sudo launchctl bootstrap system /Library/LaunchDaemons/limit.maxfiles.plist
```

### 问题3: 配置已存在

**解决方案**: 先删除旧配置，再重新创建：

```bash
sudo rm -f /Library/LaunchDaemons/limit.maxfiles.plist
rm -f ~/Library/LaunchAgents/limit.maxfiles.plist
# 然后重新运行修复脚本
```

---

## 📝 创建的配置文件位置

修复后会创建以下文件：

1. **系统级配置**: `/Library/LaunchDaemons/limit.maxfiles.plist`
   - 系统启动时自动加载
   - 需要管理员权限

2. **用户级配置**: `~/Library/LaunchAgents/limit.maxfiles.plist`
   - 用户登录时自动加载
   - 不需要管理员权限

3. **Shell 配置**: `~/.zshrc` 或 `~/.bash_profile`
   - 每次打开终端时自动执行 `ulimit -n 65536`

---

## ✅ 修复完成检查清单

- [ ] 运行了修复脚本或手动执行了所有命令
- [ ] 重启了 Docker Desktop
- [ ] 验证了 `ulimit -n` 显示 65536
- [ ] 验证了 `launchctl limit maxfiles` 显示 65536 200000
- [ ] 验证了 `docker info` 不再报错
- [ ] （可选）重启了 Mac 以确保系统级配置生效

---

## 🎉 完成！

修复完成后，Docker 应该不再出现 "too many open files" 错误。如果仍有问题，请查看 `DOCKER_FILE_LIMITS_FIX.md` 获取更多帮助。

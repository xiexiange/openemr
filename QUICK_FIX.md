# Docker "too many open files" 快速修复指南

## 🚀 快速修复（5分钟）

### 步骤1: 运行修复脚本

在终端中执行（需要输入管理员密码）：

```bash
cd /Users/xian/Documents/创业app/doctor/openemr
./fix-docker-file-limits.sh
```

### 步骤2: 重启 Docker Desktop

修复完成后，重启 Docker Desktop：

```bash
# 完全退出 Docker Desktop
osascript -e 'quit app "Docker"'

# 等待几秒
sleep 3

# 重新启动
open -a Docker

# 等待 Docker 完全启动（约30-60秒）
```

### 步骤3: 验证修复

```bash
# 检查限制是否已提高
launchctl limit maxfiles

# 应该显示类似：
# maxfiles    65536           200000

# 检查 Docker 是否正常
docker info
```

---

## ⚡ 临时快速修复（无需重启）

如果急需使用 Docker，可以先临时提高限制：

```bash
# 1. 临时提高当前会话的限制
ulimit -n 65536

# 2. 重启 Docker Desktop
osascript -e 'quit app "Docker"' && sleep 3 && open -a Docker

# 3. 等待 Docker 启动后验证
docker info
```

**注意**: 这只是临时方案，关闭终端后需要重新设置。

---

## 🔧 如果脚本无法运行

### 手动执行以下命令：

```bash
# 1. 创建系统配置文件
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

# 2. 加载配置
sudo launchctl load -w /Library/LaunchDaemons/limit.maxfiles.plist 2>/dev/null || sudo launchctl bootstrap system /Library/LaunchDaemons/limit.maxfiles.plist

# 3. 设置当前限制
sudo launchctl limit maxfiles 65536 200000
ulimit -n 65536

# 4. 重启 Docker Desktop
osascript -e 'quit app "Docker"' && sleep 3 && open -a Docker
```

---

## ✅ 验证修复成功

运行以下命令检查：

```bash
# 应该显示 65536 或更高
ulimit -n

# 应该显示类似：maxfiles    65536           200000
launchctl limit maxfiles

# Docker 应该正常工作，不再报错
docker info
```

---

## 🆘 如果问题仍然存在

1. **重启 Mac** - 系统级配置需要重启才能完全生效
2. **检查详细指南** - 查看 `DOCKER_FILE_LIMITS_FIX.md`
3. **清理 Docker 资源** - 停止不需要的容器

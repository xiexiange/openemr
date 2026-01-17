# Docker "too many open files" 问题解决方案

## 🔴 问题描述

Docker Desktop 报错：
```
accept unix /Users/xian/Library/Containers/com.docker.docker/Data/stats.sock: 
accept: too many open files in system
```

## 🔍 问题原因

macOS 系统默认的文件描述符限制太低（通常为 256），而 Docker 需要打开大量文件来监控容器和系统状态。当打开的文件数超过系统限制时，就会出现这个错误。

## ✅ 解决方案

### 方法1: 使用自动修复脚本（推荐）

```bash
cd /Users/xian/Documents/创业app/doctor/openemr
./fix-docker-file-limits.sh
```

脚本会自动：
1. 创建系统级配置文件提高限制
2. 创建用户级配置文件
3. 更新 shell 配置
4. 应用新的限制

**修复后需要重启 Docker Desktop：**

```bash
# 完全退出 Docker Desktop
osascript -e 'quit app "Docker"'

# 等待几秒
sleep 3

# 重新启动
open -a Docker
```

### 方法2: 手动修复步骤

#### 步骤1: 创建系统级限制配置

```bash
sudo tee /Library/LaunchDaemons/limit.maxfiles.plist > /dev/null <<EOF
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
```

#### 步骤2: 加载系统配置

```bash
sudo launchctl load -w /Library/LaunchDaemons/limit.maxfiles.plist
# 或者在新版 macOS 上使用：
sudo launchctl bootstrap system /Library/LaunchDaemons/limit.maxfiles.plist
```

#### 步骤3: 设置当前会话限制

```bash
sudo launchctl limit maxfiles 65536 200000
ulimit -n 65536
```

#### 步骤4: 创建用户级配置（可选，用于新终端）

```bash
mkdir -p ~/Library/LaunchAgents

cat > ~/Library/LaunchAgents/limit.maxfiles.plist <<EOF
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

launchctl load -w ~/Library/LaunchAgents/limit.maxfiles.plist
```

#### 步骤5: 更新 Shell 配置

添加到 `~/.zshrc` 或 `~/.bash_profile`：

```bash
echo "ulimit -n 65536" >> ~/.zshrc
source ~/.zshrc
```

#### 步骤6: 重启 Docker Desktop

```bash
# 完全退出
osascript -e 'quit app "Docker"'

# 等待
sleep 3

# 重新启动
open -a Docker
```

## 🔍 验证修复

### 检查当前限制

```bash
# 检查用户限制
ulimit -n

# 检查系统限制
launchctl limit maxfiles

# 应该显示类似：
# maxfiles    65536           200000
```

### 检查 Docker 状态

```bash
# 等待 Docker 完全启动后
docker info

# 应该不再有 "too many open files" 错误
```

## 🛠️ 如果问题仍然存在

### 1. 重启 Mac

系统级配置需要重启才能完全生效：

```bash
sudo reboot
```

### 2. 检查是否有其他进程占用大量文件

```bash
# 查看打开文件最多的进程
lsof | awk '{print $1}' | sort | uniq -c | sort -rn | head -10
```

### 3. 清理 Docker 资源

```bash
# 停止所有容器
docker stop $(docker ps -aq)

# 清理未使用的资源
docker system prune -a --volumes -f
```

### 4. 检查系统日志

```bash
# 查看 Docker 相关错误
log show --predicate 'process == "com.docker.backend"' --last 1h | grep -i "too many"
```

## 📊 推荐的限制值

| 环境 | 软限制 | 硬限制 |
|------|--------|--------|
| 开发环境 | 65536 | 200000 |
| 生产环境 | 65536 | 200000 |
| 高负载环境 | 100000 | 500000 |

## 🔄 临时解决方案（快速修复）

如果急需使用 Docker，可以临时提高限制：

```bash
# 临时提高当前会话的限制
ulimit -n 65536

# 然后重启 Docker Desktop
osascript -e 'quit app "Docker"' && sleep 3 && open -a Docker
```

**注意**: 这只是临时方案，新终端窗口不会继承这个限制。

## 📝 预防措施

1. **定期清理 Docker 资源**
   ```bash
   docker system prune -f
   ```

2. **监控文件描述符使用**
   ```bash
   # 查看当前打开的文件数
   lsof | wc -l
   
   # 查看 Docker 相关进程打开的文件
   lsof | grep -i docker | wc -l
   ```

3. **限制容器数量**
   - 不要同时运行过多容器
   - 及时停止不需要的容器

## 🆘 获取帮助

如果以上方法都无法解决问题：

1. **查看 Docker Desktop 日志**
   - Docker Desktop → Troubleshoot → View logs

2. **检查系统资源**
   ```bash
   # 检查内存
   vm_stat
   
   # 检查磁盘空间
   df -h
   ```

3. **联系 Docker 支持**
   - https://www.docker.com/support/

## 📚 相关资源

- [Docker Desktop for Mac 文档](https://docs.docker.com/desktop/mac/)
- [macOS 文件描述符限制](https://developer.apple.com/documentation/kernel/file_descriptors)

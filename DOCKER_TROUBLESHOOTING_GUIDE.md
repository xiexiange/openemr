# Docker 环境排查与彻底清理指南

## 一、问题排查步骤

### 1. 检查 Docker Desktop 状态

#### macOS 检查方法：

```bash
# 检查 Docker Desktop 是否在运行
ps aux | grep -i "Docker Desktop" | grep -v grep

# 检查 Docker 守护进程
ps aux | grep -i dockerd | grep -v grep

# 检查 Docker 命令是否可用
which docker
docker --version
```

#### 如果 Docker 命令不可用：

1. **打开 Docker Desktop 应用**
   - 在 Launchpad 或 Applications 中查找 "Docker"
   - 如果找不到，需要重新安装 Docker Desktop

2. **检查 Docker Desktop 是否启动**
   - 查看菜单栏是否有 Docker 图标（鲸鱼图标）
   - 点击图标，查看状态是否为 "Docker Desktop is running"

3. **如果 Docker Desktop 未启动**
   ```bash
   # 尝试启动 Docker Desktop
   open -a Docker
   
   # 等待 30-60 秒让 Docker 完全启动
   # 然后检查状态
   docker info
   ```

### 2. 检查当前容器状态

```bash
# 查看所有容器（包括已停止的）
docker ps -a

# 查看运行中的容器
docker ps

# 查看容器日志（如果有容器在运行）
docker compose logs
```

### 3. 检查 Docker Compose 状态

```bash
# 进入项目目录
cd /Users/xian/Documents/创业app/doctor/openemr

# 检查开发环境
cd docker/development-easy
docker compose ps

# 或检查生产环境
cd docker/production
docker compose ps

# 查看详细日志
docker compose logs --tail=100
```

### 4. 常见问题诊断

#### 问题1: 端口被占用
```bash
# 检查端口占用情况
lsof -i :8300  # OpenEMR HTTP
lsof -i :9300  # OpenEMR HTTPS
lsof -i :8320  # MySQL
lsof -i :8310  # phpMyAdmin

# 如果端口被占用，可以：
# 1. 停止占用端口的进程
# 2. 或修改 docker-compose.yml 中的端口映射
```

#### 问题2: 磁盘空间不足
```bash
# 检查 Docker 磁盘使用情况
docker system df

# 查看详细占用
docker system df -v
```

#### 问题3: 容器启动失败
```bash
# 查看容器详细日志
docker compose logs openemr
docker compose logs mysql

# 查看容器状态
docker compose ps -a
```

---

## 二、彻底清理步骤

### ⚠️ 警告：以下操作会删除所有数据，请确保已备份重要数据！

### 步骤1: 停止所有容器

```bash
# 进入项目目录
cd /Users/xian/Documents/创业app/doctor/openemr

# 停止开发环境
cd docker/development-easy
docker compose down

# 停止生产环境（如果有）
cd ../production
docker compose down

# 停止所有相关容器（强制）
docker stop $(docker ps -aq --filter "name=openemr")
docker stop $(docker ps -aq --filter "name=mysql")
```

### 步骤2: 删除所有容器

```bash
# 删除所有已停止的容器
docker container prune -f

# 或删除特定容器
docker rm -f $(docker ps -aq --filter "name=openemr")
docker rm -f $(docker ps -aq --filter "name=mysql")
```

### 步骤3: 删除所有卷（数据）

```bash
# ⚠️ 危险操作：这会删除所有数据卷！
# 查看要删除的卷
docker volume ls

# 删除 OpenEMR 相关的卷
docker volume rm $(docker volume ls -q --filter "name=openemr")
docker volume rm $(docker volume ls -q --filter "name=database")
docker volume rm $(docker volume ls -q --filter "name=site")
docker volume rm $(docker volume ls -q --filter "name=log")

# 或删除所有未使用的卷
docker volume prune -f
```

### 步骤4: 删除镜像（可选）

```bash
# 查看镜像
docker images

# 删除 OpenEMR 相关镜像
docker rmi $(docker images -q --filter "reference=openemr/*")
docker rmi $(docker images -q --filter "reference=mariadb:*")

# 或删除所有未使用的镜像
docker image prune -a -f
```

### 步骤5: 清理系统（推荐）

```bash
# 清理所有未使用的资源（容器、网络、镜像、构建缓存）
docker system prune -a --volumes -f

# 这会删除：
# - 所有停止的容器
# - 所有未使用的网络
# - 所有未使用的镜像（不仅仅是悬空镜像）
# - 所有未使用的卷
# - 所有构建缓存
```

### 步骤6: 重置 Docker Desktop（如果问题严重）

如果上述步骤仍无法解决问题：

1. **完全退出 Docker Desktop**
   - 点击菜单栏 Docker 图标
   - 选择 "Quit Docker Desktop"

2. **清理 Docker 数据目录**
   ```bash
   # 备份重要数据后，删除 Docker 数据
   # ⚠️ 这会删除所有 Docker 数据！
   rm -rf ~/Library/Containers/com.docker.docker
   rm -rf ~/Library/Application\ Support/Docker\ Desktop
   ```

3. **重新启动 Docker Desktop**
   - 打开 Docker Desktop 应用
   - 等待完全启动

---

## 三、一键清理脚本

创建一个清理脚本 `cleanup-docker.sh`：

```bash
#!/bin/bash

echo "⚠️  开始清理 Docker 环境..."
echo ""

# 停止所有容器
echo "1. 停止所有容器..."
cd /Users/xian/Documents/创业app/doctor/openemr/docker/development-easy 2>/dev/null
docker compose down 2>/dev/null

cd /Users/xian/Documents/创业app/doctor/openemr/docker/production 2>/dev/null
docker compose down 2>/dev/null

# 停止所有相关容器
docker stop $(docker ps -aq --filter "name=openemr") 2>/dev/null
docker stop $(docker ps -aq --filter "name=mysql") 2>/dev/null

# 删除所有容器
echo "2. 删除所有容器..."
docker container prune -f

# 删除所有卷
echo "3. 删除所有数据卷..."
docker volume prune -f

# 清理系统
echo "4. 清理系统资源..."
docker system prune -a --volumes -f

echo ""
echo "✅ 清理完成！"
echo ""
echo "当前 Docker 状态："
docker ps -a
docker volume ls
docker images | head -5
```

使用方法：
```bash
chmod +x cleanup-docker.sh
./cleanup-docker.sh
```

---

## 四、清理后重新启动

### 1. 确认 Docker Desktop 运行正常

```bash
# 检查 Docker 状态
docker info

# 应该看到类似输出，没有错误
```

### 2. 重新启动 OpenEMR

```bash
cd /Users/xian/Documents/创业app/doctor/openemr/docker/development-easy

# 启动服务
docker compose up -d

# 查看日志
docker compose logs -f
```

### 3. 验证服务状态

```bash
# 检查容器状态
docker compose ps

# 应该看到所有服务都是 "Up" 状态
# - mysql: Up
# - openemr: Up
```

---

## 五、常见错误及解决方案

### 错误1: "Cannot connect to the Docker daemon"

**原因**: Docker Desktop 未启动或 Docker 守护进程未运行

**解决**:
```bash
# 启动 Docker Desktop
open -a Docker

# 等待启动完成（30-60秒）
# 然后重试命令
```

### 错误2: "Port is already allocated"

**原因**: 端口被其他进程占用

**解决**:
```bash
# 查找占用端口的进程
lsof -i :8300

# 停止占用进程，或修改 docker-compose.yml 中的端口
```

### 错误3: "No space left on device"

**原因**: Docker 磁盘空间不足

**解决**:
```bash
# 清理未使用的资源
docker system prune -a --volumes -f

# 检查磁盘空间
df -h
```

### 错误4: 容器一直重启

**原因**: 容器启动失败，Docker 自动重启

**解决**:
```bash
# 查看详细日志
docker compose logs openemr

# 检查配置文件
# 检查环境变量
# 检查数据卷权限
```

---

## 六、预防措施

### 1. 定期清理

```bash
# 每周运行一次清理未使用的资源
docker system prune -f

# 每月运行一次深度清理
docker system prune -a --volumes -f
```

### 2. 监控磁盘使用

```bash
# 定期检查 Docker 磁盘使用
docker system df
```

### 3. 备份重要数据

```bash
# 备份数据卷
docker run --rm -v openemr_sitevolume:/data -v $(pwd):/backup \
  alpine tar czf /backup/sitevolume-backup.tar.gz -C /data .

# 恢复数据卷
docker run --rm -v openemr_sitevolume:/data -v $(pwd):/backup \
  alpine tar xzf /backup/sitevolume-backup.tar.gz -C /data
```

---

## 七、获取帮助

如果以上步骤都无法解决问题：

1. **查看 Docker Desktop 日志**
   - Docker Desktop → Troubleshoot → View logs

2. **检查系统日志**
   ```bash
   # macOS 系统日志
   log show --predicate 'process == "com.docker.backend"' --last 1h
   ```

3. **重启 Docker Desktop**
   - 完全退出 Docker Desktop
   - 等待 10 秒
   - 重新启动

4. **重新安装 Docker Desktop**
   - 从官网下载最新版本
   - 完全卸载旧版本
   - 安装新版本

---

## 快速参考命令

```bash
# 查看状态
docker ps -a                    # 所有容器
docker compose ps              # Compose 服务状态
docker system df               # 磁盘使用

# 清理
docker compose down            # 停止并删除容器
docker compose down -v         # 停止并删除容器和卷
docker system prune -a --volumes -f  # 彻底清理

# 日志
docker compose logs            # 查看所有日志
docker compose logs openemr    # 查看特定服务日志
docker compose logs -f         # 实时日志

# 重启
docker compose restart         # 重启服务
docker compose up -d           # 启动服务
```

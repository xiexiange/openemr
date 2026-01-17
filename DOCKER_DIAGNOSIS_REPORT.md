# Docker 环境诊断报告

**诊断时间**: $(date)

## 🔍 诊断结果

### ❌ 主要问题

1. **Docker Desktop 未安装或已卸载**
   - Docker Desktop 应用不在 `/Applications/` 目录
   - 系统信息显示 Docker 应用在垃圾桶中（可能已被删除）
   - Docker 命令不可用

2. **残留文件检测**
   - 发现 Docker 容器数据目录: `~/Library/Containers/com.docker.docker`
   - 这可能包含旧的配置和数据

### ✅ 发现的信息

- Docker Compose 配置文件存在: `docker/development-easy/docker-compose.yml`
- 项目结构完整

---

## 🛠️ 解决方案

### 方案1: 重新安装 Docker Desktop（推荐）

#### 步骤1: 清理残留文件

```bash
# 删除 Docker 残留数据（如果存在）
rm -rf ~/Library/Containers/com.docker.docker
rm -rf ~/Library/Application\ Support/Docker\ Desktop
rm -rf ~/Library/Group\ Containers/group.com.docker
rm -rf ~/Library/Preferences/com.docker.docker.plist

# 清理命令行工具（如果存在）
sudo rm -rf /usr/local/bin/docker*
sudo rm -rf /usr/local/bin/docker-compose*
```

#### 步骤2: 下载并安装 Docker Desktop

1. **访问 Docker 官网**
   - 打开: https://www.docker.com/products/docker-desktop/
   - 或直接下载: https://desktop.docker.com/mac/main/arm64/Docker.dmg

2. **安装步骤**
   - 下载 Docker Desktop for Mac (Apple Silicon)
   - 打开下载的 `.dmg` 文件
   - 将 Docker 拖拽到 Applications 文件夹
   - 打开 Applications，双击 Docker 启动
   - 按照安装向导完成设置

3. **首次启动配置**
   - 允许 Docker Desktop 在系统设置中运行
   - 等待 Docker 引擎启动（菜单栏会出现 Docker 图标）
   - 可能需要输入管理员密码

#### 步骤3: 验证安装

```bash
# 检查 Docker 命令
docker --version

# 检查 Docker 状态
docker info

# 测试运行
docker run hello-world
```

---

### 方案2: 使用 Homebrew 安装（替代方案）

```bash
# 安装 Homebrew（如果未安装）
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# 使用 Homebrew 安装 Docker Desktop
brew install --cask docker

# 启动 Docker Desktop
open -a Docker
```

---

## 📋 安装后的配置

### 1. 配置 Docker 镜像加速器（国内用户推荐）

打开 Docker Desktop → Settings → Docker Engine，添加：

```json
{
  "registry-mirrors": [
    "https://docker.mirrors.ustc.edu.cn",
    "https://hub-mirror.c.163.com",
    "https://mirror.baidubce.com"
  ]
}
```

点击 "Apply & Restart"

### 2. 验证配置

```bash
docker info | grep -A 10 "Registry Mirrors"
```

### 3. 启动 OpenEMR 项目

```bash
cd /Users/xian/Documents/创业app/doctor/openemr/docker/development-easy
docker compose up -d
```

---

## 🔧 如果安装后仍有问题

### 问题1: Docker Desktop 无法启动

**解决方案**:
1. 检查系统要求（macOS 版本、内存等）
2. 重启 Mac
3. 检查系统安全设置，允许 Docker 运行

### 问题2: 端口被占用

**解决方案**:
```bash
# 检查端口占用
lsof -i :8300
lsof -i :9300
lsof -i :8320

# 停止占用端口的进程，或修改 docker-compose.yml 中的端口
```

### 问题3: 权限问题

**解决方案**:
```bash
# 将用户添加到 docker 组（通常 macOS 不需要）
# 或使用 sudo（不推荐）
```

---

## 📝 快速检查清单

安装完成后，运行以下命令验证：

```bash
# 1. 检查 Docker 版本
docker --version

# 2. 检查 Docker Compose 版本
docker compose version

# 3. 检查 Docker 守护进程
docker info

# 4. 运行诊断脚本
cd /Users/xian/Documents/创业app/doctor/openemr
./diagnose-docker.sh
```

---

## 🚀 下一步

1. ✅ 安装 Docker Desktop
2. ✅ 配置镜像加速器
3. ✅ 运行诊断脚本确认环境正常
4. ✅ 启动 OpenEMR 项目

---

## 📞 获取帮助

如果遇到问题：

1. **查看 Docker Desktop 日志**
   - Docker Desktop → Troubleshoot → View logs

2. **查看系统日志**
   ```bash
   log show --predicate 'process == "com.docker.backend"' --last 1h
   ```

3. **Docker 官方文档**
   - https://docs.docker.com/desktop/install/mac-install/

4. **重新运行诊断**
   ```bash
   ./diagnose-docker.sh
   ```

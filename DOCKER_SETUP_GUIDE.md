# OpenEMR Docker 启动指南

## 问题诊断

如果遇到 "failed to do request" 或 "context deadline exceeded" 错误，通常是网络连接问题，无法访问Docker Hub。

## 解决方案

### 方案1：配置Docker镜像加速器（推荐）

#### macOS Docker Desktop配置步骤：

1. **打开Docker Desktop应用**
2. **点击右上角的设置图标**（齿轮⚙️）
3. **选择左侧菜单的 "Docker Engine"**
4. **在JSON编辑器中添加镜像加速器配置**

   在现有配置中添加 `registry-mirrors` 字段：

   ```json
   {
     "builder": {
       "gc": {
         "defaultKeepStorage": "20GB",
         "enabled": true
       }
     },
     "experimental": false,
     "registry-mirrors": [
       "https://docker.mirrors.ustc.edu.cn",
       "https://hub-mirror.c.163.com",
       "https://mirror.baidubce.com"
     ]
   }
   ```

5. **点击 "Apply & Restart" 按钮**
6. **等待Docker重启完成**（通常需要30秒-1分钟）

#### 验证配置是否生效：

```bash
docker info | grep -A 10 "Registry Mirrors"
```

如果看到镜像地址列表，说明配置成功。

### 方案2：使用代理（推荐，如果镜像加速器不可用）

如果你有可用的HTTP/HTTPS代理：

1. **打开Docker Desktop**
2. **Settings -> Docker Engine**
3. **移除 registry-mirrors 配置**（删除整个 registry-mirrors 数组）
4. **Settings -> Resources -> Proxies**
5. **选择 "Manual proxy configuration"**
6. **填入代理地址和端口**（例如：http://127.0.0.1:7890）
7. **点击 "Apply & Restart"**

### 方案2.1：如果镜像加速器域名无法解析

如果配置了镜像加速器但出现 "no such host" 错误：

1. **打开Docker Desktop**
2. **Settings -> Docker Engine**
3. **移除 registry-mirrors 配置**（删除整个 registry-mirrors 数组）
4. **点击 "Apply & Restart"**
5. 然后配置代理（见方案2）或使用其他镜像源

### 方案3：使用简化版配置

项目提供了一个简化版配置 `docker/development-easy-light/docker-compose.yml`，只包含核心服务：

- MySQL数据库
- OpenEMR应用
- phpMyAdmin

不包含：
- Selenium（测试工具）
- CouchDB（文档存储）
- LDAP（认证服务）
- Mailpit（邮件测试）

使用简化版：

```bash
cd docker/development-easy-light
docker compose up -d
```

## 启动OpenEMR

配置完成后，使用以下命令启动：

### 完整版（推荐用于开发）：

```bash
cd docker/development-easy
docker compose up -d
```

### 简化版（快速启动）：

```bash
cd docker/development-easy-light
docker compose up -d
```

## 访问地址

启动成功后：

- **OpenEMR应用**: 
  - HTTP: http://localhost:8300
  - HTTPS: https://localhost:9300
- **默认登录**: 
  - 用户名: `admin`
  - 密码: `pass`
- **phpMyAdmin**: http://localhost:8310
  - 用户名: `openemr`
  - 密码: `openemr`

## 查看日志

如果启动遇到问题，查看日志：

```bash
# 查看所有服务日志
docker compose logs

# 查看特定服务日志
docker compose logs openemr
docker compose logs mysql
```

## 停止服务

```bash
docker compose down
```

## 完全清理（包括数据）

```bash
docker compose down -v
```

## 常见问题

### Q: 镜像拉取很慢或失败？
A: 确保已配置镜像加速器（见方案1）

### Q: 端口被占用？
A: 检查端口占用：
```bash
lsof -i :8300
lsof -i :9300
```

### Q: SSL证书错误？
A: 首次访问HTTPS地址时，浏览器会提示证书不安全，这是正常的（开发环境使用自签名证书），选择"继续访问"即可。

### Q: 容器启动失败？
A: 查看详细日志：
```bash
docker compose logs -f
```

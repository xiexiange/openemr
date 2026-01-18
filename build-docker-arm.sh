#!/bin/bash
# OpenEMR Docker 镜像打包脚本 - ARM 架构版本
# 用途：打包 ARM 架构的 Docker 镜像，用于 ARM ECS 服务器
# 输出目录：项目根目录的 output 目录

set -e

# 颜色输出
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 脚本目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
OUTPUT_DIR="$PROJECT_ROOT/output"
TEMP_DIR="$OUTPUT_DIR/temp"

# 目标平台（ARM 架构）
TARGET_PLATFORM="linux/arm64"

# 选择 tar 命令（优先使用 GNU tar）
TAR_CMD="tar"
if command -v gtar >/dev/null 2>&1; then
    TAR_CMD="gtar"
fi

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  OpenEMR Docker 镜像打包工具 (ARM64)${NC}"
echo -e "${BLUE}  目标平台: ${TARGET_PLATFORM}${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# 清理并创建输出目录
rm -rf "$TEMP_DIR"
mkdir -p "$OUTPUT_DIR" "$TEMP_DIR"

# 定义需要打包的镜像
IMAGES=(
    "phpmyadmin:latest"
)

# 检查镜像平台的函数
check_image_platform() {
    local IMAGE=$1
    local PLATFORM=$(docker image inspect "$IMAGE" --format '{{.Architecture}}' 2>/dev/null || echo "")
    
    if [ -z "$PLATFORM" ]; then
        return 1  # 镜像不存在
    fi
    
    # 检查是否是 arm64/aarch64
    if [[ "$PLATFORM" == "arm64" ]] || [[ "$PLATFORM" == "aarch64" ]]; then
        return 0  # 是 ARM64
    else
        return 2  # 是其他平台
    fi
}

echo -e "${BLUE}步骤 1: 检查镜像是否存在及平台...${NC}"
MISSING_IMAGES=()
WRONG_PLATFORM_IMAGES=()
for IMAGE in "${IMAGES[@]}"; do
    if docker image inspect "$IMAGE" >/dev/null 2>&1; then
        if check_image_platform "$IMAGE"; then
            PLATFORM=$(docker image inspect "$IMAGE" --format '{{.Architecture}}')
            echo -e "${GREEN}✓${NC} $IMAGE (平台: $PLATFORM)"
        else
            PLATFORM=$(docker image inspect "$IMAGE" --format '{{.Architecture}}')
            echo -e "${YELLOW}⚠${NC} $IMAGE (平台: $PLATFORM, 需要 ${TARGET_PLATFORM})"
            WRONG_PLATFORM_IMAGES+=("$IMAGE")
        fi
    else
        echo -e "${YELLOW}✗${NC} $IMAGE (未找到)"
        MISSING_IMAGES+=("$IMAGE")
    fi
done
echo ""

# 处理缺失的镜像
if [ ${#MISSING_IMAGES[@]} -gt 0 ] || [ ${#WRONG_PLATFORM_IMAGES[@]} -gt 0 ]; then
    echo -e "${BLUE}步骤 2: 拉取 ${TARGET_PLATFORM} 平台镜像...${NC}"
    
    for IMAGE in "${MISSING_IMAGES[@]}" "${WRONG_PLATFORM_IMAGES[@]}"; do
        echo -e "${YELLOW}删除本地镜像（确保拉取 ARM64 版本）: $IMAGE${NC}"
        docker rmi "$IMAGE" 2>/dev/null || true
        
        echo -e "${BLUE}正在拉取 ${TARGET_PLATFORM} 版本的镜像: $IMAGE${NC}"
        docker pull --platform "${TARGET_PLATFORM}" "$IMAGE"
        
        if [ $? -ne 0 ]; then
            echo -e "${RED}错误: 无法拉取 ${TARGET_PLATFORM} 版本的镜像: $IMAGE${NC}"
            exit 1
        fi
        
        # 验证拉取的镜像架构
        ACTUAL_ARCH=$(docker image inspect "$IMAGE" --format '{{.Architecture}}' 2>/dev/null || echo "")
        if [[ "$ACTUAL_ARCH" != "arm64" ]] && [[ "$ACTUAL_ARCH" != "aarch64" ]]; then
            echo -e "${YELLOW}警告: 拉取的镜像架构不是 ARM64: $IMAGE (实际: $ACTUAL_ARCH)${NC}"
            echo -e "${YELLOW}提示: 如果镜像不支持 ARM64，可能需要使用其他版本${NC}"
        else
            echo -e "${GREEN}✓ 已确认镜像架构为 ARM64: $IMAGE${NC}"
        fi
    done
    echo ""
fi

echo -e "${BLUE}步骤 3: 导出镜像为 tar 文件...${NC}"
SAVED_TAR_FILES=()
for IMAGE in "${IMAGES[@]}"; do
    IMAGE_NAME=$(echo "$IMAGE" | sed 's/\//_/g' | sed 's/:/_/g')
    TAR_FILE="$TEMP_DIR/${IMAGE_NAME}-arm64.tar"
    
    echo -e "${BLUE}正在导出镜像: $IMAGE -> ${TAR_FILE}${NC}"
    
    # 尝试使用 docker save
    if docker save -o "$TAR_FILE" "$IMAGE" 2>/dev/null; then
        if [ -f "$TAR_FILE" ] && [ -s "$TAR_FILE" ]; then
            SAVED_TAR_FILES+=("$TAR_FILE")
            TAR_SIZE=$(du -h "$TAR_FILE" | cut -f1)
            echo -e "${GREEN}✓ 已导出镜像: ${TAR_FILE} (大小: ${TAR_SIZE})${NC}"
        else
            echo -e "${YELLOW}⚠ 导出文件为空或不存在，尝试容器导出方法...${NC}"
            # 使用容器导出作为备选
            CONTAINER_ID=$(docker create --platform linux/arm64 "$IMAGE" 2>/dev/null || echo "")
            if [ -n "$CONTAINER_ID" ]; then
                docker export "$CONTAINER_ID" | gzip > "${TAR_FILE}.gz"
                docker rm "$CONTAINER_ID" 2>/dev/null || true
                if [ -f "${TAR_FILE}.gz" ] && [ -s "${TAR_FILE}.gz" ]; then
                    SAVED_TAR_FILES+=("${TAR_FILE}.gz")
                    TAR_SIZE=$(du -h "${TAR_FILE}.gz" | cut -f1)
                    echo -e "${GREEN}✓ 已导出镜像（容器方式）: ${TAR_FILE}.gz (大小: ${TAR_SIZE})${NC}"
                    echo -e "${YELLOW}注意: 这是容器导出，加载时使用 docker import${NC}"
                fi
            fi
        fi
    else
        echo -e "${YELLOW}⚠ docker save 失败，尝试容器导出方法...${NC}"
        # 使用容器导出作为备选
        CONTAINER_ID=$(docker create --platform linux/arm64 "$IMAGE" 2>/dev/null || echo "")
        if [ -n "$CONTAINER_ID" ]; then
            docker export "$CONTAINER_ID" | gzip > "${TAR_FILE}.gz"
            docker rm "$CONTAINER_ID" 2>/dev/null || true
            if [ -f "${TAR_FILE}.gz" ] && [ -s "${TAR_FILE}.gz" ]; then
                SAVED_TAR_FILES+=("${TAR_FILE}.gz")
                TAR_SIZE=$(du -h "${TAR_FILE}.gz" | cut -f1)
                echo -e "${GREEN}✓ 已导出镜像（容器方式）: ${TAR_FILE}.gz (大小: ${TAR_SIZE})${NC}"
                echo -e "${YELLOW}注意: 这是容器导出，加载时使用 docker import${NC}"
            else
                echo -e "${RED}✗ 导出失败: $IMAGE${NC}"
            fi
        else
            echo -e "${RED}✗ 无法创建容器: $IMAGE${NC}"
        fi
    fi
    echo ""
done

if [ ${#SAVED_TAR_FILES[@]} -eq 0 ]; then
    echo -e "${RED}错误: 没有成功导出任何镜像${NC}"
    exit 1
fi

echo -e "${GREEN}✓ 所有镜像已导出完成${NC}"
echo ""

# 步骤 4: 创建生产环境配置文件
echo -e "${BLUE}步骤 4: 创建生产环境配置文件...${NC}"

# 创建生产环境的 docker-compose.yml（指定 ARM64 平台）
cat > "$TEMP_DIR/docker-compose.yml" <<'EOF'
# OpenEMR Docker Compose 配置 - ARM64 架构
# Use admin/pass as user/password credentials to login to openemr
services:
  mysql:
    restart: always
    image: mariadb:11.8
    platform: linux/arm64
    pull_policy: if_not_present
    command: ['mariadbd','--character-set-server=utf8mb4']
    volumes:
    - databasevolume:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
    healthcheck:
      test:
      - CMD
      - /usr/local/bin/healthcheck.sh
      - --su-mysql
      - --connect
      - --innodb_initialized
      start_period: 1m
      interval: 1m
      timeout: 5s
      retries: 3
  openemr:
    restart: always
    image: openemr/openemr:7.0.4
    platform: linux/arm64
    pull_policy: if_not_present
    ports:
    - 80:80
    - 443:443
    volumes:
    - logvolume01:/var/log
    - sitevolume:/var/www/localhost/htdocs/openemr/sites
    environment:
      MYSQL_HOST: mysql
      MYSQL_ROOT_PASS: root
      MYSQL_USER: openemr
      MYSQL_PASS: openemr
      OE_USER: admin
      OE_PASS: pass
    depends_on:
      mysql:
        condition: service_healthy
    healthcheck:
      test:
      - CMD
      - /usr/bin/curl
      - --fail
      - --insecure
      - --location
      - --show-error
      - --silent
      - https://localhost/meta/health/readyz
      start_period: 3m
      interval: 1m
      timeout: 5s
      retries: 3
volumes:
  logvolume01: {}
  sitevolume: {}
  databasevolume: {}
EOF

echo -e "${GREEN}✓${NC} docker-compose.yml (已指定平台: linux/arm64)"

# 创建 .env.example 文件
cat > "$TEMP_DIR/.env.example" <<'EOF'
# OpenEMR 环境变量配置

# MySQL Root 密码
MYSQL_ROOT_PASSWORD=root

# MySQL 用户和密码
MYSQL_USER=openemr
MYSQL_PASSWORD=openemr

# OpenEMR 管理员用户名和密码
OE_USER=admin
OE_PASS=pass

# 时区设置
TZ=Asia/Shanghai
EOF

echo -e "${GREEN}✓${NC} .env.example"

# 创建部署说明文件
cat > "$TEMP_DIR/DEPLOYMENT_README.md" <<'EOF'
# OpenEMR Docker 部署说明 - ARM64 架构

## 文件说明

- `mariadb_11.8-arm64.tar` 或 `mariadb_11.8-arm64.tar.gz`: MariaDB 镜像文件（ARM64）
- `openemr_openemr_7.0.4-arm64.tar` 或 `openemr_openemr_7.0.4-arm64.tar.gz`: OpenEMR 镜像文件（ARM64）
- `docker-compose.yml`: Docker Compose 配置文件（已指定平台为 linux/arm64）
- `.env.example`: 环境变量配置示例

## 部署步骤

### 1. 上传文件到 ARM ECS 服务器

```bash
# 在本地执行
scp output/docker-*.tar.gz root@您的ARM服务器IP:/opt/
```

### 2. 在服务器上解压

```bash
cd /opt
tar -xzf docker-*.tar.gz
```

### 3. 加载镜像

```bash
# 如果是 docker save 导出的（.tar 文件）
docker load -i mariadb_11.8-arm64.tar
docker load -i openemr_openemr_7.0.4-arm64.tar

# 如果是容器导出的（.tar.gz 文件，使用 import）
docker import mariadb_11.8-arm64.tar.gz mariadb:11.8
docker import openemr_openemr_7.0.4-arm64.tar.gz openemr/openemr:7.0.4
```

### 4. 启动服务

```bash
cd /opt
docker compose up -d --pull never
```

### 5. 验证

```bash
# 查看容器状态
docker compose ps

# 查看日志
docker compose logs -f
```

## 访问

- HTTP: http://服务器IP:80
- HTTPS: https://服务器IP:443
- 默认登录: admin / pass

## 注意事项

- 确保服务器是 ARM64 架构
- 如果使用容器导出（.tar.gz），加载时使用 `docker import` 而不是 `docker load`
- 首次启动可能需要 5-10 分钟
EOF

echo -e "${GREEN}✓${NC} DEPLOYMENT_README.md"
echo ""

# 步骤 5: 创建最终的 tar 包
echo -e "${BLUE}步骤 5: 创建最终打包文件...${NC}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
FINAL_TAR="$OUTPUT_DIR/docker-arm64-${TIMESTAMP}.tar"

cd "$TEMP_DIR"
$TAR_CMD --no-xattrs -cf "$FINAL_TAR" .

# 压缩 tar 文件
echo "正在压缩..."
gzip -f "$FINAL_TAR"
FINAL_TAR_GZ="${FINAL_TAR}.gz"

# 显示文件信息
FILE_SIZE=$(du -h "$FINAL_TAR_GZ" | cut -f1)
echo ""

# 生成 MD5 校验和
MD5_FILE="${FINAL_TAR_GZ}.md5"
if command -v md5 >/dev/null 2>&1; then
    md5 "$FINAL_TAR_GZ" > "$MD5_FILE"
elif command -v md5sum >/dev/null 2>&1; then
    md5sum "$FINAL_TAR_GZ" > "$MD5_FILE"
fi

# 清理临时目录
rm -rf "$TEMP_DIR"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  打包完成！${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}输出文件:${NC} $FINAL_TAR_GZ"
echo -e "${BLUE}文件大小:${NC} $FILE_SIZE"
if [ -f "$MD5_FILE" ]; then
    echo -e "${BLUE}MD5 校验:${NC} $MD5_FILE"
    cat "$MD5_FILE"
fi
echo ""
echo -e "${BLUE}包含的镜像（平台: ${TARGET_PLATFORM}）:${NC}"
for IMAGE in "${IMAGES[@]}"; do
    PLATFORM=$(docker image inspect "$IMAGE" --format '{{.Architecture}}' 2>/dev/null || echo "unknown")
    echo "  - $IMAGE (平台: $PLATFORM)"
done
echo ""
echo -e "${BLUE}上传到 ARM 服务器:${NC}"
echo "  scp $FINAL_TAR_GZ root@8.130.85.4:/opt/openemr"
echo ""

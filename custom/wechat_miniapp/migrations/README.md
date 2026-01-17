# 数据库迁移说明

## 迁移文件说明

1. **add_wechat_fields.sql** - 正向迁移脚本（添加微信字段）
2. **rollback_wechat_fields.sql** - 回滚脚本（MySQL 8.0+）
3. **rollback_wechat_fields_safe.sql** - 安全回滚脚本（兼容所有 MySQL 版本）

## 执行迁移前的重要提醒

### ⚠️ 重要：执行迁移前请先备份数据库！

```bash
# 方法一：使用 mysqldump 备份整个数据库
mysqldump -u root -p openemr > openemr_backup_$(date +%Y%m%d_%H%M%S).sql

# 方法二：只备份相关表
mysqldump -u root -p openemr users patient_data > openemr_users_patients_backup_$(date +%Y%m%d_%H%M%S).sql

# 方法三：在 phpMyAdmin 中导出
# 登录 phpMyAdmin -> 选择 openemr 数据库 -> 导出 -> 选择 "users" 和 "patient_data" 表 -> 执行
```

## 执行迁移

### 0. 备份数据库（重要！）

**如果使用 Docker：**
```bash
# 使用 Docker 专用备份脚本（推荐）
bash custom/wechat_miniapp/migrations/backup_before_migration_docker.sh

# 或手动备份
docker exec -i openemr_mysql mysqldump -u root -popenemr openemr > openemr_backup_$(date +%Y%m%d_%H%M%S).sql
```

**如果使用本地 MySQL：**
```bash
# 使用标准备份脚本
bash custom/wechat_miniapp/migrations/backup_before_migration.sh

# 或手动备份
mysqldump -u root -p openemr > openemr_backup_$(date +%Y%m%d_%H%M%S).sql
```

**备份脚本常见问题：**

如果备份脚本失败，可能的原因：
1. **MySQL 命令不在 PATH 中**：如果使用 Docker，请使用 `backup_before_migration_docker.sh`
2. **密码错误**：Docker 环境默认密码通常是 `openemr`
3. **容器名称不对**：检查 Docker 容器名称并修改脚本中的 `CONTAINER_NAME`

### 1. 执行正向迁移

```bash
mysql -u root -p openemr < custom/wechat_miniapp/migrations/add_wechat_fields.sql
```

或者使用 phpMyAdmin：
1. 登录 phpMyAdmin
2. 选择 `openemr` 数据库
3. 点击 "SQL" 标签
4. 复制 `add_wechat_fields.sql` 的内容并粘贴
5. 点击 "执行"

### 2. 验证迁移结果

```sql
-- 检查 users 表是否有新字段
DESCRIBE users;

-- 检查 patient_data 表是否有新字段
DESCRIBE patient_data;

-- 检查二维码表是否存在
SHOW TABLES LIKE 'wechat_qrcode';

-- 查看二维码表结构
DESCRIBE wechat_qrcode;
```

## 回滚迁移

### ⚠️ 警告：回滚操作会删除所有微信相关数据！

如果迁移出现问题，可以使用回滚脚本：

```bash
# 方法一：使用标准回滚脚本（MySQL 8.0+）
mysql -u root -p openemr < custom/wechat_miniapp/migrations/rollback_wechat_fields.sql

# 方法二：使用安全回滚脚本（推荐，兼容所有版本）
mysql -u root -p openemr < custom/wechat_miniapp/migrations/rollback_wechat_fields_safe.sql
```

### 手动回滚步骤（如果脚本执行失败）

如果自动回滚脚本执行失败，可以手动执行以下 SQL：

```sql
-- 1. 删除二维码表
DROP TABLE IF EXISTS `wechat_qrcode`;

-- 2. 删除 users 表的索引和字段
ALTER TABLE `users` DROP INDEX `idx_wechat_openid`;
ALTER TABLE `users` DROP COLUMN `wechat_openid`;
ALTER TABLE `users` DROP COLUMN `wechat_unionid`;
ALTER TABLE `users` DROP COLUMN `wechat_bind_time`;

-- 3. 删除 patient_data 表的索引和字段
ALTER TABLE `patient_data` DROP INDEX `idx_wechat_openid`;
ALTER TABLE `patient_data` DROP COLUMN `wechat_openid`;
ALTER TABLE `patient_data` DROP COLUMN `wechat_unionid`;
ALTER TABLE `patient_data` DROP COLUMN `wechat_bind_time`;
```

## 常见问题

### Q1: 迁移执行失败，提示字段已存在

**原因**：可能之前已经执行过迁移，或者字段名冲突。

**解决**：
1. 先检查字段是否存在：
   ```sql
   SHOW COLUMNS FROM users LIKE 'wechat_%';
   SHOW COLUMNS FROM patient_data LIKE 'wechat_%';
   ```
2. 如果字段已存在，可以跳过迁移，或者先执行回滚再重新迁移。

### Q2: 迁移执行失败，提示索引已存在

**原因**：索引可能在之前的迁移中已经创建。

**解决**：
1. 先检查索引是否存在：
   ```sql
   SHOW INDEX FROM users WHERE Key_name = 'idx_wechat_openid';
   SHOW INDEX FROM patient_data WHERE Key_name = 'idx_wechat_openid';
   ```
2. 如果索引已存在，可以手动删除后再执行迁移：
   ```sql
   ALTER TABLE users DROP INDEX idx_wechat_openid;
   ALTER TABLE patient_data DROP INDEX idx_wechat_openid;
   ```

### Q3: 回滚后如何恢复数据

**重要**：回滚操作会删除所有微信相关字段和数据，**无法恢复**！

**建议**：
1. **执行迁移前务必备份数据库**
2. 如果需要恢复，只能从备份中恢复
3. 如果只是需要移除字段但保留数据，需要先导出数据，删除字段，再重新导入

### Q4: 如何验证迁移是否成功

执行以下 SQL 验证：

```sql
-- 检查 users 表字段
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'openemr' 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME LIKE 'wechat_%';

-- 检查 patient_data 表字段
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'openemr' 
AND TABLE_NAME = 'patient_data' 
AND COLUMN_NAME LIKE 'wechat_%';

-- 检查二维码表
SELECT * FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'openemr' 
AND TABLE_NAME = 'wechat_qrcode';
```

## 数据备份最佳实践

### 1. 自动备份脚本

创建一个备份脚本 `backup_database.sh`：

```bash
#!/bin/bash
DB_NAME="openemr"
DB_USER="root"
BACKUP_DIR="/path/to/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/${DB_NAME}_${DATE}.sql

# 保留最近 7 天的备份
find $BACKUP_DIR -name "${DB_NAME}_*.sql" -mtime +7 -delete

echo "备份完成：$BACKUP_DIR/${DB_NAME}_${DATE}.sql"
```

### 2. 在迁移前备份

```bash
# 创建备份目录
mkdir -p ~/openemr_backups

# 备份数据库
mysqldump -u root -p openemr > ~/openemr_backups/openemr_before_wechat_migration_$(date +%Y%m%d_%H%M%S).sql

# 执行迁移
mysql -u root -p openemr < custom/wechat_miniapp/migrations/add_wechat_fields.sql
```

### 3. 从备份恢复

如果迁移出现问题，可以从备份恢复：

```bash
# 恢复数据库
mysql -u root -p openemr < ~/openemr_backups/openemr_before_wechat_migration_YYYYMMDD_HHMMSS.sql
```

## 安全建议

1. **生产环境**：
   - 先在测试环境执行迁移
   - 验证功能正常后再在生产环境执行
   - 确保有完整的数据库备份

2. **开发环境**：
   - 可以使用 Docker 快速重置数据库
   - 每次修改前先备份

3. **迁移时机**：
   - 选择业务低峰期执行
   - 通知相关用户暂停操作

---

**最后更新**：2024年

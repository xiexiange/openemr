-- 微信小程序接入 - 数据库回滚脚本（安全版本）
-- 执行方法: mysql -u root -p openemr < custom/wechat_miniapp/migrations/rollback_wechat_fields_safe.sql
-- 
-- ⚠️ 警告：此操作将删除所有微信相关字段和数据，执行前请确保已备份数据库！
-- 此版本会先检查字段是否存在，兼容所有 MySQL 版本

-- 删除二维码表
DROP TABLE IF EXISTS `wechat_qrcode`;

-- 从 users 表删除微信字段（需要先删除索引，再删除字段）
-- 检查并删除索引
SET @index_exists = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.STATISTICS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND INDEX_NAME = 'idx_wechat_openid'
);

SET @sql_index = IF(@index_exists > 0, 'ALTER TABLE `users` DROP INDEX `idx_wechat_openid`', 'SELECT 1');
PREPARE stmt FROM @sql_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并删除字段
SET @col_exists_openid = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'wechat_openid'
);

SET @sql_openid = IF(@col_exists_openid > 0, 'ALTER TABLE `users` DROP COLUMN `wechat_openid`', 'SELECT 1');
PREPARE stmt FROM @sql_openid;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists_unionid = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'wechat_unionid'
);

SET @sql_unionid = IF(@col_exists_unionid > 0, 'ALTER TABLE `users` DROP COLUMN `wechat_unionid`', 'SELECT 1');
PREPARE stmt FROM @sql_unionid;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists_bind_time = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'wechat_bind_time'
);

SET @sql_bind_time = IF(@col_exists_bind_time > 0, 'ALTER TABLE `users` DROP COLUMN `wechat_bind_time`', 'SELECT 1');
PREPARE stmt FROM @sql_bind_time;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 从 patient_data 表删除微信字段
SET @index_exists_patient = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.STATISTICS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'patient_data' 
  AND INDEX_NAME = 'idx_wechat_openid'
);

SET @sql_index_patient = IF(@index_exists_patient > 0, 'ALTER TABLE `patient_data` DROP INDEX `idx_wechat_openid`', 'SELECT 1');
PREPARE stmt FROM @sql_index_patient;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists_patient_openid = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'patient_data' 
  AND COLUMN_NAME = 'wechat_openid'
);

SET @sql_patient_openid = IF(@col_exists_patient_openid > 0, 'ALTER TABLE `patient_data` DROP COLUMN `wechat_openid`', 'SELECT 1');
PREPARE stmt FROM @sql_patient_openid;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists_patient_unionid = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'patient_data' 
  AND COLUMN_NAME = 'wechat_unionid'
);

SET @sql_patient_unionid = IF(@col_exists_patient_unionid > 0, 'ALTER TABLE `patient_data` DROP COLUMN `wechat_unionid`', 'SELECT 1');
PREPARE stmt FROM @sql_patient_unionid;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists_patient_bind_time = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'patient_data' 
  AND COLUMN_NAME = 'wechat_bind_time'
);

SET @sql_patient_bind_time = IF(@col_exists_patient_bind_time > 0, 'ALTER TABLE `patient_data` DROP COLUMN `wechat_bind_time`', 'SELECT 1');
PREPARE stmt FROM @sql_patient_bind_time;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

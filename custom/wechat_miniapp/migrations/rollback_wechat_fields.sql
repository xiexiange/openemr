-- 微信小程序接入 - 数据库回滚脚本
-- 执行方法: mysql -u root -p openemr < custom/wechat_miniapp/migrations/rollback_wechat_fields.sql
-- 
-- ⚠️ 警告：此操作将删除所有微信相关字段和数据，执行前请确保已备份数据库！

-- 删除二维码表
DROP TABLE IF EXISTS `wechat_qrcode`;

-- 从 users 表删除微信字段
ALTER TABLE `users` 
DROP INDEX IF EXISTS `idx_wechat_openid`,
DROP COLUMN IF EXISTS `wechat_openid`,
DROP COLUMN IF EXISTS `wechat_unionid`,
DROP COLUMN IF EXISTS `wechat_bind_time`;

-- 从 patient_data 表删除微信字段
ALTER TABLE `patient_data` 
DROP INDEX IF EXISTS `idx_wechat_openid`,
DROP COLUMN IF EXISTS `wechat_openid`,
DROP COLUMN IF EXISTS `wechat_unionid`,
DROP COLUMN IF EXISTS `wechat_bind_time`;

-- 注意：某些 MySQL 版本不支持 IF EXISTS，如果执行失败，请使用以下脚本（需要先检查字段是否存在）

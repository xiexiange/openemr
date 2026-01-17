-- 微信小程序接入 - 数据库迁移脚本
-- 执行方法: mysql -u root -p openemr < custom/wechat_miniapp/migrations/add_wechat_fields.sql

-- 为 users 表添加微信字段
ALTER TABLE `users` 
ADD COLUMN `wechat_openid` VARCHAR(128) UNIQUE DEFAULT NULL COMMENT '微信OpenID',
ADD COLUMN `wechat_unionid` VARCHAR(128) DEFAULT NULL COMMENT '微信UnionID',
ADD COLUMN `wechat_bind_time` DATETIME DEFAULT NULL COMMENT '微信绑定时间',
ADD INDEX `idx_wechat_openid` (`wechat_openid`);

-- 为 patient_data 表添加微信字段
ALTER TABLE `patient_data` 
ADD COLUMN `wechat_openid` VARCHAR(128) UNIQUE DEFAULT NULL COMMENT '微信OpenID',
ADD COLUMN `wechat_unionid` VARCHAR(128) DEFAULT NULL COMMENT '微信UnionID',
ADD COLUMN `wechat_bind_time` DATETIME DEFAULT NULL COMMENT '微信绑定时间',
ADD INDEX `idx_wechat_openid` (`wechat_openid`);

-- 创建二维码临时表
CREATE TABLE IF NOT EXISTS `wechat_qrcode` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NOT NULL UNIQUE COMMENT '二维码唯一标识',
  `type` ENUM('doctor', 'patient') NOT NULL COMMENT '类型：doctor=医生注册, patient=患者绑定',
  `openid` VARCHAR(128) DEFAULT NULL COMMENT '绑定的微信OpenID',
  `doctor_id` BIGINT(20) DEFAULT NULL COMMENT '关联的医生ID（患者绑定用）',
  `status` ENUM('pending', 'used', 'expired') DEFAULT 'pending' COMMENT '状态',
  `expires_at` DATETIME NOT NULL COMMENT '过期时间',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `used_at` DATETIME DEFAULT NULL COMMENT '使用时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`),
  INDEX `idx_openid` (`openid`),
  INDEX `idx_status` (`status`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信二维码表';

-- 检查迁移状态的 SQL 脚本
-- 执行方法: mysql -u root -p openemr < custom/wechat_miniapp/migrations/check_migration_status.sql

-- 检查 users 表的微信字段
SELECT 
    'users 表微信字段检查' AS '检查项',
    CASE 
        WHEN COUNT(*) = 3 THEN '✅ 所有字段已存在'
        WHEN COUNT(*) = 0 THEN '❌ 字段不存在（未迁移）'
        ELSE CONCAT('⚠️  部分字段存在 (', COUNT(*), '/3)')
    END AS '状态',
    GROUP_CONCAT(COLUMN_NAME) AS '现有字段'
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME LIKE 'wechat_%';

-- 检查 patient_data 表的微信字段
SELECT 
    'patient_data 表微信字段检查' AS '检查项',
    CASE 
        WHEN COUNT(*) = 3 THEN '✅ 所有字段已存在'
        WHEN COUNT(*) = 0 THEN '❌ 字段不存在（未迁移）'
        ELSE CONCAT('⚠️  部分字段存在 (', COUNT(*), '/3)')
    END AS '状态',
    GROUP_CONCAT(COLUMN_NAME) AS '现有字段'
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'patient_data' 
AND COLUMN_NAME LIKE 'wechat_%';

-- 检查二维码表
SELECT 
    'wechat_qrcode 表检查' AS '检查项',
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ 表已存在'
        ELSE '❌ 表不存在（未迁移）'
    END AS '状态',
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('表中有 ', (SELECT COUNT(*) FROM wechat_qrcode), ' 条记录')
        ELSE NULL
    END AS '备注'
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'wechat_qrcode';

-- 检查索引
SELECT 
    '索引检查' AS '检查项',
    TABLE_NAME AS '表名',
    INDEX_NAME AS '索引名',
    CASE 
        WHEN INDEX_NAME = 'idx_wechat_openid' THEN '✅ 微信 OpenID 索引已存在'
        ELSE INDEX_NAME
    END AS '状态'
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('users', 'patient_data')
AND INDEX_NAME = 'idx_wechat_openid';

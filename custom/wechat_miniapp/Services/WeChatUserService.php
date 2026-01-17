<?php

/**
 * WeChat User Service
 * 
 * 处理微信医生用户的注册和绑定
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Custom\WeChat\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\UserService;

class WeChatUserService
{
    private $userService;
    
    public function __construct()
    {
        $this->userService = new UserService();
    }
    
    /**
     * 通过微信OpenID查找用户
     * @param string $openid
     * @return array|null
     */
    public function getUserByWeChatOpenId($openid)
    {
        $sql = "SELECT * FROM users WHERE wechat_openid = ? AND active = 1";
        $result = QueryUtils::sqlQuery($sql, [$openid]);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * 注册医生（通过微信）
     * @param array $data 包含：openid, unionid, fname, lname, email, phone, specialty等
     * @return array ['success' => bool, 'user_id' => int, 'uuid' => string, 'error' => string]
     */
    public function registerDoctorByWeChat($data)
    {
        // 检查OpenID是否已绑定
        if ($this->getUserByWeChatOpenId($data['openid'])) {
            return [
                'success' => false,
                'error' => '该微信账号已绑定其他医生账号'
            ];
        }
        
        // 生成用户名（基于微信OpenID或邮箱）
        $username = $this->generateUsername($data);
        
        // 检查用户名是否已存在
        if ($this->userService->getUserByUsername($username)) {
            $username = $username . '_' . time();
        }
        
        // 创建用户UUID
        $uuid = (new UuidRegistry(['table_name' => 'users']))->createUuid();
        
        // 插入用户数据
        $sql = "INSERT INTO users SET 
                uuid = ?,
                username = ?,
                password = 'NoLongerUsed',
                fname = ?,
                lname = ?,
                email = ?,
                phone = ?,
                phonecell = ?,
                specialty = ?,
                authorized = 1,
                active = 1,
                wechat_openid = ?,
                wechat_unionid = ?,
                wechat_bind_time = NOW(),
                date = NOW()";
        
        $params = [
            $uuid,
            $username,
            $data['fname'] ?? '',
            $data['lname'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['phonecell'] ?? $data['phone'] ?? '',
            $data['specialty'] ?? '',
            $data['openid'],
            $data['unionid'] ?? null
        ];
        
        QueryUtils::sqlStatement($sql, $params);
        $userId = QueryUtils::getLastInsertId();
        
        // 创建用户安全记录（密码相关）
        $this->createUserSecure($userId, $username);
        
        // 设置默认ACL权限（医生权限）
        $this->setDefaultDoctorAcl($userId);
        
        return [
            'success' => true,
            'user_id' => $userId,
            'uuid' => UuidRegistry::uuidToString($uuid),
            'username' => $username
        ];
    }
    
    /**
     * 绑定微信到现有医生账号
     * @param int $userId
     * @param string $openid
     * @param string|null $unionid
     * @return bool
     */
    public function bindWeChatToDoctor($userId, $openid, $unionid = null)
    {
        // 检查OpenID是否已被其他账号使用
        $existing = $this->getUserByWeChatOpenId($openid);
        if ($existing && $existing['id'] != $userId) {
            return false;
        }
        
        $sql = "UPDATE users SET 
                wechat_openid = ?,
                wechat_unionid = ?,
                wechat_bind_time = NOW()
                WHERE id = ?";
        
        return QueryUtils::sqlStatement($sql, [$openid, $unionid, $userId]);
    }
    
    /**
     * 生成用户名
     * @param array $data
     * @return string
     */
    private function generateUsername($data)
    {
        // 优先使用邮箱前缀，否则使用姓名拼音
        if (!empty($data['email'])) {
            return explode('@', $data['email'])[0];
        }
        
        // 使用姓名生成（简化版，实际应使用拼音转换）
        $name = ($data['fname'] ?? '') . ($data['lname'] ?? '');
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . rand(100, 999);
    }
    
    /**
     * 创建用户安全记录
     * @param int $userId
     * @param string $username
     */
    private function createUserSecure($userId, $username)
    {
        // 生成随机密码（微信登录不需要密码，但系统要求）
        $password = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users_secure (id, username, password) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE password = ?";
        QueryUtils::sqlStatement($sql, [$userId, $username, $hashedPassword, $hashedPassword]);
    }
    
    /**
     * 设置默认医生ACL权限
     * @param int $userId
     */
    private function setDefaultDoctorAcl($userId)
    {
        // 这里需要根据你的ACL系统设置默认权限
        // 示例：添加基本的医生权限
        // 具体实现需要查看你的ACL配置
        // 暂时留空，需要根据实际ACL系统实现
    }
}

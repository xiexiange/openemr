<?php

/**
 * WeChat QR Code Service
 * 
 * 处理微信二维码的生成、验证和管理
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Custom\WeChat\Services;

use OpenEMR\Common\Database\QueryUtils;

class WeChatQrCodeService
{
    /**
     * 生成二维码
     * @param string $type 'doctor' 或 'patient'
     * @param int|null $doctorId 医生ID（患者绑定时需要）
     * @param int $expireMinutes 过期时间（分钟），默认10分钟
     * @return array ['code' => string, 'qr_url' => string, 'expires_at' => string]
     */
    public function generateQrCode($type, $doctorId = null, $expireMinutes = 10)
    {
        // 生成唯一码
        $code = $this->generateUniqueCode();
        
        // 计算过期时间
        $expiresAt = date('Y-m-d H:i:s', time() + $expireMinutes * 60);
        
        // 插入数据库
        $sql = "INSERT INTO wechat_qrcode (code, type, doctor_id, status, expires_at) 
                VALUES (?, ?, ?, 'pending', ?)";
        QueryUtils::sqlStatement($sql, [$code, $type, $doctorId, $expiresAt]);
        
        // 生成二维码URL（小程序扫码后跳转的URL）
        $qrUrl = $this->buildQrCodeUrl($code);
        
        return [
            'code' => $code,
            'qr_url' => $qrUrl,
            'expires_at' => $expiresAt
        ];
    }
    
    /**
     * 验证二维码
     * @param string $code
     * @return array|null
     */
    public function validateQrCode($code)
    {
        $sql = "SELECT * FROM wechat_qrcode WHERE code = ? AND status = 'pending'";
        $result = QueryUtils::sqlQuery($sql, [$code]);
        
        if (empty($result)) {
            return null;
        }
        
        $qrData = $result[0];
        
        // 检查是否过期
        if (strtotime($qrData['expires_at']) < time()) {
            // 标记为过期
            $this->markAsExpired($code);
            return null;
        }
        
        return $qrData;
    }
    
    /**
     * 绑定微信OpenID到二维码
     * @param string $code
     * @param string $openid
     * @return bool
     */
    public function bindOpenId($code, $openid)
    {
        $sql = "UPDATE wechat_qrcode 
                SET openid = ?, status = 'used', used_at = NOW() 
                WHERE code = ? AND status = 'pending'";
        return QueryUtils::sqlStatement($sql, [$openid, $code]);
    }
    
    /**
     * 标记二维码为已使用
     * @param string $code
     */
    public function markAsUsed($code)
    {
        $sql = "UPDATE wechat_qrcode SET status = 'used', used_at = NOW() WHERE code = ?";
        QueryUtils::sqlStatement($sql, [$code]);
    }
    
    /**
     * 标记二维码为过期
     * @param string $code
     */
    public function markAsExpired($code)
    {
        $sql = "UPDATE wechat_qrcode SET status = 'expired' WHERE code = ?";
        QueryUtils::sqlStatement($sql, [$code]);
    }
    
    /**
     * 生成唯一码
     * @return string
     */
    private function generateUniqueCode()
    {
        do {
            $code = 'WX' . strtoupper(uniqid()) . rand(1000, 9999);
            $sql = "SELECT COUNT(*) as cnt FROM wechat_qrcode WHERE code = ?";
            $result = QueryUtils::sqlQuery($sql, [$code]);
        } while (!empty($result) && $result[0]['cnt'] > 0);
        
        return $code;
    }
    
    /**
     * 构建二维码URL
     * @param string $code
     * @return string
     */
    private function buildQrCodeUrl($code)
    {
        // 这里返回小程序可以扫描的URL格式
        // 实际使用时，小程序会解析这个URL获取code参数
        $baseUrl = $GLOBALS['webroot'] ?? 'https://your-openemr-host';
        return $baseUrl . '/custom/wechat_miniapp/qr/' . $code;
    }
}

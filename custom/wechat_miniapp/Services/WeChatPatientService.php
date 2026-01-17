<?php

/**
 * WeChat Patient Service
 * 
 * 处理微信患者的注册和绑定
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Custom\WeChat\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientService;

class WeChatPatientService
{
    private $patientService;
    
    public function __construct()
    {
        $this->patientService = new PatientService();
    }
    
    /**
     * 通过微信OpenID查找患者
     * @param string $openid
     * @return array|null
     */
    public function getPatientByWeChatOpenId($openid)
    {
        $sql = "SELECT * FROM patient_data WHERE wechat_openid = ?";
        $result = QueryUtils::sqlQuery($sql, [$openid]);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * 注册患者（通过微信）
     * @param array $data 包含：openid, unionid, fname, lname, DOB, sex, phone, email等
     * @param int|null $doctorId 绑定的医生ID
     * @return array ['success' => bool, 'pid' => int, 'uuid' => string, 'error' => string]
     */
    public function registerPatientByWeChat($data, $doctorId = null)
    {
        // 检查OpenID是否已绑定
        if ($this->getPatientByWeChatOpenId($data['openid'])) {
            return [
                'success' => false,
                'error' => '该微信账号已绑定其他患者账号'
            ];
        }
        
        // 使用PatientService创建患者
        $patientData = [
            'fname' => $data['fname'] ?? '',
            'lname' => $data['lname'] ?? '',
            'mname' => $data['mname'] ?? '',
            'DOB' => $data['DOB'] ?? '',
            'sex' => $data['sex'] ?? '',
            'phone_home' => $data['phone'] ?? '',
            'phone_cell' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'street' => $data['street'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'providerID' => $doctorId, // 绑定医生
        ];
        
        $result = $this->patientService->insert($patientData);
        
        if ($result->hasErrors()) {
            return [
                'success' => false,
                'error' => implode(', ', $result->getValidationMessages())
            ];
        }
        
        $patient = $result->getData()[0];
        
        // 更新微信字段（因为PatientService可能不支持这些字段）
        $this->updateWeChatFields($patient['pid'], $data['openid'], $data['unionid'] ?? null);
        
        return [
            'success' => true,
            'pid' => $patient['pid'],
            'uuid' => $patient['uuid']
        ];
    }
    
    /**
     * 绑定微信到现有患者
     * @param int $pid
     * @param string $openid
     * @param string|null $unionid
     * @return bool
     */
    public function bindWeChatToPatient($pid, $openid, $unionid = null)
    {
        // 检查OpenID是否已被其他患者使用
        $existing = $this->getPatientByWeChatOpenId($openid);
        if ($existing && $existing['pid'] != $pid) {
            return false;
        }
        
        $sql = "UPDATE patient_data SET 
                wechat_openid = ?,
                wechat_unionid = ?,
                wechat_bind_time = NOW()
                WHERE pid = ?";
        
        return QueryUtils::sqlStatement($sql, [$openid, $unionid, $pid]);
    }
    
    /**
     * 绑定患者到医生
     * @param int $pid
     * @param int $doctorId
     * @return bool
     */
    public function bindPatientToDoctor($pid, $doctorId)
    {
        $sql = "UPDATE patient_data SET providerID = ? WHERE pid = ?";
        return QueryUtils::sqlStatement($sql, [$doctorId, $pid]);
    }
    
    /**
     * 更新微信字段
     * @param int $pid
     * @param string $openid
     * @param string|null $unionid
     */
    private function updateWeChatFields($pid, $openid, $unionid = null)
    {
        $sql = "UPDATE patient_data SET 
                wechat_openid = ?,
                wechat_unionid = ?,
                wechat_bind_time = NOW()
                WHERE pid = ?";
        QueryUtils::sqlStatement($sql, [$openid, $unionid, $pid]);
    }
}

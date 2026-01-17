<?php

/**
 * WeChat Miniapp Controller
 * 
 * 微信小程序API控制器
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Custom\WeChat\Controllers;

use OpenEMR\Custom\WeChat\Services\WeChatQrCodeService;
use OpenEMR\Custom\WeChat\Services\WeChatUserService;
use OpenEMR\Custom\WeChat\Services\WeChatPatientService;
use OpenEMR\Common\Http\HttpRestRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

class WeChatMiniappController
{
    private $qrCodeService;
    private $userService;
    private $patientService;
    
    public function __construct()
    {
        $this->qrCodeService = new WeChatQrCodeService();
        $this->userService = new WeChatUserService();
        $this->patientService = new WeChatPatientService();
    }
    
    /**
     * 生成医生注册二维码
     * GET /apis/default/api/wechat/qrcode/doctor
     */
    public function generateDoctorQrCode(HttpRestRequest $request)
    {
        $result = $this->qrCodeService->generateQrCode('doctor');
        
        return new JsonResponse([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * 生成患者绑定二维码（需要医生ID）
     * GET /apis/default/api/wechat/qrcode/patient?doctor_id=123
     */
    public function generatePatientQrCode(HttpRestRequest $request)
    {
        $doctorId = $request->getQueryParams()['doctor_id'] ?? null;
        
        if (!$doctorId) {
            return new JsonResponse([
                'success' => false,
                'error' => '医生ID不能为空'
            ], 400);
        }
        
        $result = $this->qrCodeService->generateQrCode('patient', $doctorId);
        
        return new JsonResponse([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * 扫码后绑定微信OpenID
     * POST /apis/default/api/wechat/bind
     * Body: { "code": "WX...", "openid": "...", "unionid": "..." }
     */
    public function bindWeChat(HttpRestRequest $request)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $code = $data['code'] ?? '';
        $openid = $data['openid'] ?? '';
        $unionid = $data['unionid'] ?? null;
        
        if (empty($code) || empty($openid)) {
            return new JsonResponse([
                'success' => false,
                'error' => '参数不完整'
            ], 400);
        }
        
        // 验证二维码
        $qrData = $this->qrCodeService->validateQrCode($code);
        if (!$qrData) {
            return new JsonResponse([
                'success' => false,
                'error' => '二维码无效或已过期'
            ], 400);
        }
        
        // 绑定OpenID到二维码
        $this->qrCodeService->bindOpenId($code, $openid);
        
        return new JsonResponse([
            'success' => true,
            'data' => [
                'type' => $qrData['type'],
                'doctor_id' => $qrData['doctor_id']
            ]
        ]);
    }
    
    /**
     * 医生注册
     * POST /apis/default/api/wechat/doctor/register
     * Body: { "code": "WX...", "openid": "...", "unionid": "...", "fname": "...", ... }
     */
    public function registerDoctor(HttpRestRequest $request)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        // 验证二维码
        if (!empty($data['code'])) {
            $qrData = $this->qrCodeService->validateQrCode($data['code']);
            if (!$qrData || $qrData['type'] !== 'doctor') {
                return new JsonResponse([
                    'success' => false,
                    'error' => '二维码无效或类型不匹配'
                ], 400);
            }
            
            // 验证OpenID是否匹配
            if ($qrData['openid'] !== $data['openid']) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'OpenID不匹配'
                ], 400);
            }
        }
        
        // 注册医生
        $result = $this->userService->registerDoctorByWeChat($data);
        
        if (!$result['success']) {
            return new JsonResponse([
                'success' => false,
                'error' => $result['error'] ?? '注册失败'
            ], 400);
        }
        
        // 标记二维码为已使用
        if (!empty($data['code'])) {
            $this->qrCodeService->markAsUsed($data['code']);
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * 患者注册并绑定医生
     * POST /apis/default/api/wechat/patient/register
     * Body: { "code": "WX...", "openid": "...", "fname": "...", ... }
     */
    public function registerPatient(HttpRestRequest $request)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        // 验证二维码
        if (!empty($data['code'])) {
            $qrData = $this->qrCodeService->validateQrCode($data['code']);
            if (!$qrData || $qrData['type'] !== 'patient') {
                return new JsonResponse([
                    'success' => false,
                    'error' => '二维码无效或类型不匹配'
                ], 400);
            }
            
            // 验证OpenID是否匹配
            if ($qrData['openid'] !== $data['openid']) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'OpenID不匹配'
                ], 400);
            }
            
            // 获取医生ID
            $doctorId = $qrData['doctor_id'];
        } else {
            $doctorId = $data['doctor_id'] ?? null;
        }
        
        if (!$doctorId) {
            return new JsonResponse([
                'success' => false,
                'error' => '医生ID不能为空'
            ], 400);
        }
        
        // 注册患者
        $result = $this->patientService->registerPatientByWeChat($data, $doctorId);
        
        if (!$result['success']) {
            return new JsonResponse([
                'success' => false,
                'error' => $result['error'] ?? '注册失败'
            ], 400);
        }
        
        // 标记二维码为已使用
        if (!empty($data['code'])) {
            $this->qrCodeService->markAsUsed($data['code']);
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * 检查二维码状态
     * GET /apis/default/api/wechat/qrcode/status?code=WX...
     */
    public function checkQrCodeStatus(HttpRestRequest $request)
    {
        $code = $request->getQueryParams()['code'] ?? '';
        
        if (empty($code)) {
            return new JsonResponse([
                'success' => false,
                'error' => '二维码code不能为空'
            ], 400);
        }
        
        $qrData = $this->qrCodeService->validateQrCode($code);
        
        if (!$qrData) {
            return new JsonResponse([
                'success' => false,
                'status' => 'invalid'
            ]);
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => [
                'status' => $qrData['status'],
                'type' => $qrData['type'],
                'openid' => $qrData['openid'],
                'expires_at' => $qrData['expires_at']
            ]
        ]);
    }
}

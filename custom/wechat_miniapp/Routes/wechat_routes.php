<?php

/**
 * WeChat Miniapp Routes
 * 
 * 微信小程序API路由定义
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Custom\WeChat\Controllers\WeChatMiniappController;
use OpenEMR\Common\Http\HttpRestRequest;

return [
    // 生成医生注册二维码
    "GET /api/wechat/qrcode/doctor" => function (HttpRestRequest $request) {
        $controller = new WeChatMiniappController();
        return $controller->generateDoctorQrCode($request);
    },
    
    // 生成患者绑定二维码
    "GET /api/wechat/qrcode/patient" => function (HttpRestRequest $request) {
        $controller = new WeChatMiniappController();
        return $controller->generatePatientQrCode($request);
    },
    
    // 绑定微信
    "POST /api/wechat/bind" => function (HttpRestRequest $request) {
        $controller = new WeChatMiniappController();
        return $controller->bindWeChat($request);
    },
    
    // 医生注册
    "POST /api/wechat/doctor/register" => function (HttpRestRequest $request) {
        $controller = new WeChatMiniappController();
        return $controller->registerDoctor($request);
    },
    
    // 患者注册
    "POST /api/wechat/patient/register" => function (HttpRestRequest $request) {
        $controller = new WeChatMiniappController();
        return $controller->registerPatient($request);
    },
    
    // 检查二维码状态
    "GET /api/wechat/qrcode/status" => function (HttpRestRequest $request) {
        $controller = new WeChatMiniappController();
        return $controller->checkQrCodeStatus($request);
    }
];

<?php

/**
 * WeChat Miniapp Bootstrap
 * 
 * 注册微信小程序事件监听器
 * 在 OpenEMR 启动时自动加载
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// 这个文件需要在 OpenEMR 的全局 bootstrap 中引入
// 或者通过 autoload 机制自动加载

use OpenEMR\Custom\WeChat\Listeners\WeChatRouteListener;
use OpenEMR\Core\OEHttpKernel;

// 注册事件监听器
if (class_exists('OpenEMR\Core\OEHttpKernel')) {
    $kernel = $GLOBALS['kernel'] ?? null;
    if ($kernel instanceof OEHttpKernel) {
        $dispatcher = $kernel->getEventDispatcher();
        $dispatcher->addSubscriber(new WeChatRouteListener());
    }
}

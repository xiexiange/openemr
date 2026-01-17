<?php

/**
 * WeChat Route Listener
 * 
 * 监听路由创建事件，添加微信小程序路由
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Custom\WeChat\Listeners;

use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WeChatRouteListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            RestApiCreateEvent::EVENT_HANDLE => 'onRouteCreate'
        ];
    }
    
    public function onRouteCreate(RestApiCreateEvent $event)
    {
        // 加载微信小程序路由
        $wechatRoutesFile = __DIR__ . '/../Routes/wechat_routes.php';
        
        if (file_exists($wechatRoutesFile)) {
            $wechatRoutes = require $wechatRoutesFile;
            
            // 将微信路由添加到路由映射
            foreach ($wechatRoutes as $route => $handler) {
                $event->addToRouteMap($route, $handler);
            }
        }
    }
}

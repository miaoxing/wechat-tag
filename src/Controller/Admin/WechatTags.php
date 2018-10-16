<?php

namespace Miaoxing\WechatTag\Controller\Admin;

use Miaoxing\Plugin\BaseController;

class WechatTags extends BaseController
{
    protected $controllerName = '微信标签管理';

    protected $actionPermissions = [
        'syncFromWechat' => '同步',
    ];

    public function syncFromWechatAction()
    {
        $ret = wei()->wechatTag->syncFromWechat();

        return $ret;
    }
}

<?php

namespace Miaoxing\WechatTag;

use Miaoxing\Plugin\BasePlugin;
use Miaoxing\UserTag\Service\UserTagModel;

class Plugin extends BasePlugin
{
    /**
     * {@inheritdoc}
     */
    protected $name = '微信用户标签';

    public function onBeforeUserTagSave(UserTagModel $userTag)
    {
        $api = wei()->wechatAccount->getCurrentAccount()->createApiService();
        if ($userTag->outId) {
            return $api->callAuth('cgi-bin/tags/update', [
                'tag' => [
                    'id' => $userTag->outId,
                    'name' => $userTag->name,
                ],
            ]);
        }
        
        $ret = $api->callAuth('cgi-bin/tags/create', [
            'tag' => [
                'name' => $userTag->name,
            ],
        ]);
        if ($ret['code'] === 1) {
            $userTag->outId = $ret['tag']['id'];
        }
        return $ret;
    }
}

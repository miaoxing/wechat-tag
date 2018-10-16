<?php

namespace Miaoxing\WechatTag;

use Miaoxing\Plugin\BasePlugin;
use Miaoxing\UserTag\Service\UserTagModel;
use Wei\RetTrait;

class Plugin extends BasePlugin
{
    use RetTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = '微信用户标签';

    public function onBeforeUserTagSave(UserTagModel $userTag)
    {
        $api = wei()->wechatAccount->getCurrentAccount()->createApiService();
        if ($userTag->outId) {
            return $api->callAuth('cgi-bin/tags/update', [
                'tag' => ['id' => $userTag->outId, 'name' => $userTag->name],
            ]);
        }

        $ret = $api->callAuth('cgi-bin/tags/create', [
            'tag' => ['name' => $userTag->name],
        ]);
        if ($ret['code'] === 1) {
            $userTag->outId = $ret['tag']['id'];
        }
        return $ret;
    }

    public function onBeforeUserTagDestroy(UserTagModel $userTag)
    {
        if (!$userTag->outId) {
            return $this->suc();
        }

        $api = wei()->wechatAccount->getCurrentAccount()->createApiService();
        $ret = $api->callAuth('cgi-bin/tags/delete', [
            'tag' => ['id' => $userTag->outId],
        ]);
        if ($ret['code'] !== 1) {
            return $ret;
        }

        wei()->userTagsUserModel()->delete(['tag_id' => $userTag->id]);
        return $this->suc();
    }
}

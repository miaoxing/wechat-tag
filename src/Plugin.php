<?php

namespace Miaoxing\WechatTag;

use Miaoxing\Plugin\BasePlugin;
use Miaoxing\User\Service\UserModel;
use Miaoxing\UserTag\Service\UserTagModel;
use Miaoxing\UserTag\Service\UserTagsUserModel;
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

    public function onBeforeUserTagsUserUpdate(
        UserModel $user,
        UserTagsUserModel $userTagsUsers,
        $addTagIds,
        $deleteTagIds
    ) {
        if (!$user->wechatOpenId) {
            return;
        }

        $ret = $this->addTagIds($user, $addTagIds);
        if ($ret['code'] !== 1) {
            return $ret;
        }

        $ret = $this->deleteTagIds($user, $deleteTagIds);
        if ($ret['code'] !== 1) {
            return $ret;
        }
    }

    protected function addTagIds(UserModel $user, $tagIds)
    {
        $outIds = array_filter(wei()->userTagModel()->findAllByIds($tagIds)->getAll('outId'));
        foreach ($outIds as $outId) {
            $api = wei()->wechatAccount->getCurrentAccount()->createApiService();
            $ret = $api->callAuth('cgi-bin/tags/members/batchtagging', [
                'openid_list' => [$user->wechatOpenId],
                'tagid' => $outId,
            ]);
            if ($ret['code'] !== 1) {
                return $ret;
            }
        }
        return $this->suc();
    }

    protected function deleteTagIds(UserModel $user, $tagIds)
    {
        $outIds = array_filter(wei()->userTagModel()->findAllByIds($tagIds)->getAll('outId'));
        foreach ($outIds as $outId) {
            $api = wei()->wechatAccount->getCurrentAccount()->createApiService();
            $ret = $api->callAuth('cgi-bin/tags/members/batchuntagging', [
                'openid_list' => [$user->wechatOpenId],
                'tagid' => $outId,
            ]);
            if ($ret['code'] !== 1) {
                return $ret;
            }
        }
        return $this->suc();
    }
}

<?php

namespace Miaoxing\WechatTag\Service;

use Miaoxing\Plugin\BaseService;
use Wei\RetTrait;

/**
 * WechatTag
 */
class WechatTag extends BaseService
{
    use RetTrait;

    public function syncFromWechat()
    {
        $counts = [
            true => 0,
            false => 0,
        ];

        $api = wei()->wechatAccount->getCurrentAccount()->createApiService();
        $ret = $api->getTags();
        if ($ret['code'] !== 1) {
            return $ret;
        }

        foreach ($ret['tags'] as $tag) {
            $tagModel = wei()->userTagModel()->findOrInit(['out_id' => $tag['id']]);
            $counts[$tagModel->isNew()]++;
            $tagModel->save([
                'name' => $tag['name'],
                'userCount' => $tag['count'],
            ]);
        }

        return $this->suc(['同步完成,共新增了%s个,更新了%s个', $counts[true], $counts[false]]);
    }
}

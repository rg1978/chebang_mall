<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 */
class sysrate_api_append_reply {

    /**
     * 接口作用说明
     */
    public $apiDescription = '买家对追评进行回复(解释说明)';

    public function getParams()
    {
        $return['params'] = array(
            'rate_id' => ['type'=>'int','valid'=>'required|numeric', 'description'=>'评论ID'],
            'shop_id' => ['type'=>'int','valid'=>'required|numeric', 'description'=>'商家的店铺ID'],
            'reply_content' => ['type'=>'string','valid'=>'required|min:5|max:300', 'description'=>'追评内容'],
        );

        return $return;
    }

    public function reply($params)
    {
        $set = [
            'append_reply_content' => $params['reply_content'],
            'is_reply' => '1',
            'reply_time'=> time(),
        ];

        $where = [
            'rate_id' => $params['rate_id'],
            'is_reply' => '0',
            'shop_id' => $params['shop_id'],
        ];

        return app::get('sysrate')->model('append')->update($set, $where);
    }
}


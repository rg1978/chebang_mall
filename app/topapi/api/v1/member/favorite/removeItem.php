<?php
/**
 * topapi
 *
 * -- member.favorite.item.remove
 * -- 移除收藏商品
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_favorite_removeItem implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '移除收藏商品';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'item_id' => ['type'=>'int', 'valid'=>'required|numeric', 'example'=>'', 'desc'=>'收藏商品的商品ID'],
        ];
    }

    /**
     * @return bool true 返回操作成功状态
     */
    public function handle($params)
    {
        return app::get('topapi')->rpcCall('user.itemcollect.del', $params);
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":null}';
    }
}


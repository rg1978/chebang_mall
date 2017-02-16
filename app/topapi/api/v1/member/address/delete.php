<?php
/**
 * topapi
 *
 * -- member.address.delete
 * -- 删除收货地址
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_address_delete implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '删除收货地址';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'addr_id'  => ['type'=>'string', 'valid'=>'required|numeric', 'example'=>'', 'desc'=>'收货地址ID'],
        ];
    }

    /**
     * @return bool true 成功
     */
    public function handle($params)
    {
        return app::get('topm')->rpcCall('user.address.del',$params);
    }
}


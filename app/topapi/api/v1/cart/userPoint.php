<?php
/**
 * topapi
 *
 * -- cart.user.point
 * -- 获取购物车信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_userPoint implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '购物车结算页处理会员积分';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'total_price' => ['type'=>'string',  'valid'=>'required', 'example'=>'fastbuy', 'desc'=>'购物车金额'],
            'post_fee'    => ['type'=>'float',  'valid'=>'',       'example'=>'true',    'desc'=>'邮费'],
        ];
    }

    /**
     */
    public function handle($params)
    {

        return kernel::single('topapi_cart_checkout')->userPoint($params['user_id'], $params['total_price'], $params['post_fee']);
    }

    public function returnJson()
    {
    }

}


<?php
/**
 * topapi
 *
 * -- cart.checkout
 * -- 订单确认页面信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_totalCart implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '购物车结算页计算金额';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'shipping' =>                  ['type'=>'array', 'valid'=>'', 'desc'=>'配送信息(shipping[3(shop_id)][shipping_type]=express)', 'example'=>'', 'msg'=>''],
            'current_shop_id' =>           ['type'=>'int', 'valid'=>'', 'desc'=>'选中店铺的id', 'example'=>'', 'msg'=>''],
            'addr_id' =>                   ['type'=>'int', 'valid'=>'required', 'desc'=>'选中地址的id', 'example'=>'', 'msg'=>''],
            'mode' =>                      ['type'=>'string', 'valid'=>'in:cart,fastbuy', 'desc'=>'购物车类型,默认是cart', 'example'=>'cart', 'msg'=>''],
//          'shipping.list.shop_id',
//          'shipping.list.shipping_type',
        ];
    }

    /**
     */
    public function handle($params)
    {
        return kernel::single('topapi_cart_checkout')->totalWithPoint($params);
    }

    public function __transferShipping($shipping)
    {

        return array_bind_key($shipping, 'shop_id');
    }


    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"allPayment":"0.16","allPostfee":"0.00","disCountfee":"0.00","shop":{"3":{"payment":"0.16","total_fee":0.16,"discount_fee":0,"obtain_point_fee":1,"post_fee":"0.00","totalWeight":16}}}}';
    }

}


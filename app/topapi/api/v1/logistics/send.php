<?php
/**
 * topapi
 *
 * -- logistics.send
 * -- 售后退换货，用户回寄商品给商家
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_logistics_send implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '售后退换货，用户回寄商品给商家';

    public function setParams()
    {
        $return['params'] = array(
            'aftersales_bn' => ['type'=>'string',     'valid'=>'required',        'desc'=>'申请售后的订单编号'],
            'corp_code'     => ['type'=>'string',     'valid'=>'required',        'desc'=>'物流公司代码'],
            'logi_name'     => ['type'=>'string',     'valid'=>'required_if:corp_code,other',  'desc'=>'物流公司名称', 'msg'=>'请填写物流公司名称'],
            'logi_no'       => ['type'=>'string',     'valid'=>'required|min:6|max:20', 'desc'=>'物流单号', 'msg'=>'请填写物流单号|请填写正确的物流单号|请填写正确的物流单号'],
            'receiver_address' => ['type'=>'string',  'valid'=>'required',        'desc'=>'换货发货地址', 'msg'=>'请填写收货地址'],
            'mobile'        => ['type'=>'string',     'valid'=>'required|mobile', 'desc'=>'手机号', 'msg'=>'请填写手机号|请填写正确的手机号'],
        );

        return $return;
    }

    public function handle($params)
    {
        app::get('topapi')->rpcCall('aftersales.send.back', $params);
        return null;
    }
}


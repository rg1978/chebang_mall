<?php
/**
 * topapi
 *
 * -- trade.cancel.create
 * -- 获取会员取消订单
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_cancel_create implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取会员取消订单';

    /**
     * 定义API传入的应用级参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'desc'=>'订单id'],
            'cancel_reason' => ['type'=>'string', 'valid'=>'required|max:50', 'desc'=>'订单取消原因', 'msg'=>'请填写取消原因|取消原因最多50个字'],
        );

        return $return;
    }

    /**
     * @return string tid 取消的订单ID
     */
    public function handle($params)
    {
        $apiParams['user_id'] = $params['user_id'];
        $apiParams['cancel_reason'] = $params['cancel_reason'];
        $apiParams['tid'] = $params['tid'];

        $tid = app::get('topapi')->rpcCall('trade.cancel.create',$apiParams);

        return ['tid'=>$tid];
    }
}


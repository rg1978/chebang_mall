<?php
/**
 * topapi
 *
 * -- trade.confirm
 * -- 会员确认收货交易完成
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_confirm implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员确认收货交易完成';

    /**
     * 定义API传入的应用级参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'desc'=>'订单id'],
        );

        return $return;
    }

    public function handle($params)
    {
        $apiParams['user_id'] = $params['user_id'];
        $apiParams['tid'] = $params['tid'];
        return app::get('topapi')->rpcCall('trade.confirm',$apiParams);
    }
}


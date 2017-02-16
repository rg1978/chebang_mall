<?php
/**
 * topapi
 *
 * -- cart.count
 * -- 统计购物车信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_countCart implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = "统计购物车商品数量";

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [ ];
    }

    /**
     * @return int variety 商品种类
     * @return int number 商品数量
     */
    public function handle($params)
    {
        $userId        = $params['user_id'];
        $requestParams['user_id'] = $userId;
        $responseData = app::get('topapi')->rpcCall('trade.cart.getCount', $requestParams);
        return $responseData;
    }

    public function returnJson()
    {
        return '';
    }

}



<?php
/**
 * topapi
 *
 * -- cart.del
 * -- 删除购物车条目
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_delCart implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '删除购物车条目';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'cart_id' => ['type'=>'string','valid'=>'','desc'=>'购物车id,多个数据用逗号隔开', 'example'=>'33,44,12,3' ],
            'mode'    => ['type'=>'string','valid'=>'','desc'=>'是否是立即购买',              'example'=>''           ],
        ];
    }

    /**
     */
    public function handle($params)
    {
        $userId = $params['user_id'];
        $requestParams = [
            'user_id' => $userId,
            'cart_id' => $params['cart_id'],
            'mode' => $params['mode']
        ];
        $responseData = app::get('topapi')->rpcCall('trade.cart.delete', $requestParams);
        if($responseData)
        {
            return [
                'cart_id' => $params['cart_id'],
            ];
        }
        else
        {
            throw new \LogicException('删除购物车商品失败！');
        }
        return $responseData;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"cart_id":"8"}}';
    }

}


<?php
/**
 * topapi
 *
 * -- cart.update
 * -- 更新购物车信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_updateCart implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新购物车数据';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'obj_type'           => ['type'=>'string', 'valid'=>'',                'desc'=>'数据类型,默认是item', 'example'=>'item'],
            'mode'               => ['type'=>'string', 'valid'=>'in:fastbuy,cart', 'desc'=>'是否立即购买，值为fastbuy或者cart',        'example'=>'cart'],
            'cart_params' => ['type'=>'json', 'valid'=>'required', 'example'=>'[{"cart_id":1,"is_checked":1,"selected_promotion":13,"totalQuantity":2},{"cart_id":7,"is_checked":1,"selected_promotion":0,"totalQuantity":1}]', 'desc'=>'购物车更新参数', 'params' => [
                //单个购物车项需要的参数
                'cart_id'      => ['type'=>'int',  'valid'=>'required|min:1', 'example'=>'5', 'desc'=>'购物车项cart_id'],
                'is_checked'   => ['type'=>'bool',  'valid'=>'required|in:1,0', 'example'=>'1', 'desc'=>'是否选中,1：选中，0：不选中'],
                'selected_promotion'  => ['type'=>'bool',  'valid'=>'', 'example'=>'9', 'desc'=>'购物车项选则的促销id'],
                'totalQuantity' => ['type'=>'int',  'valid'=>'required|min:1', 'example'=>'3', 'desc'=>'购物车项商品数量'],
            ]],
        ];
    }

    public function handle($params)
    {
        $obj_type = $params['obj_type'] ? :'item';
        $mode = $params['mode'] ? :'cart';

        $postData = json_decode($params['cart_params'], 1);
        try
        {
            $updataData = [];
            foreach ($postData as $v)
            {
                $updateParams = [
                    'user_id' => $params['user_id'],
                    'mode' => $mode,
                    'obj_type' => $obj_type,
                    'cart_id' => intval($v['cart_id']),
                    'totalQuantity' => intval($v['totalQuantity']),
                    'selected_promotion' => intval($v['selected_promotion']),
                    'is_checked' => $v['is_checked'] ? '1' : '0',
                ];
                $responseData = app::get('topapi')->rpcCall('trade.cart.update', $updateParams);
                if( $responseData === false )
                {
                    throw new \LogicException('更新购物车错误！');
                }
                $updataData[] = [
                    'cart_id' => $updateParams['cart_id'],
                    'totalQuantity' => $updateParams['totalQuantity'],
                    'selected_promotion' => $updateParams['selected_promotion'],
                    'is_checked' => $updateParams['is_checked'],
                ];
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            throw new \LogicException($msg);
        }
        return $updataData;

    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":[{"cart_id":10,"totalQuantity":3,"selected_promotion":14,"is_checked":"1"}]}';
    }

}


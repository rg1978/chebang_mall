<?php
/**
 * topapi
 *
 * -- cart.get.basic
 * -- 获取购物车信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_getBasicCart implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取购物车信息（基础内容）';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'mode'        => ['type'=>'string',  'valid'=>'in:cart,fastbuy', 'default'=>'cart', 'example'=>'fastbuy', 'desc'=>'购物车类型(立即购买，购物车),默认是cart'],
            'needInvalid' => ['type'=>'boolean', 'valid'=>'',                'default'=>'true', 'example'=>'true',    'desc'=>'是否需要显示失效商品，默认显示'],
            'platform'    => ['type'=>'string',  'valid'=>'in:pc,wap',       'default'=>'pc',   'example'=>'true',    'desc'=>'平台,默认是pc'],
        ];
    }

    /**
     * @return int list.cart_id 购物车编号
     * @return string list.user_ident 购物车会员hash
     * @return int list.user_id 会员id
     * @return int list.shop_id 商品店铺id
     * @return string list.obj_type 购物车条目类型
     * @return string list.obj_ident 购物车对象ident
     * @return int list.item_id 商品id
     * @return int list.sku_id 库存单位id
     * @return string list.title 商品名称
     * @return string list.image_default_id 商品默认图片
     * @return int list.quantity 商品数量
     * @return bool list.is_checked 是否被选中
     * @return int list.package_id 组合促销id
     * @return mix list.params
     * @return int list.selected_promotion 选中的优惠
     * @return int list.create_time 创建时间
     * @return int list.modified_time 最后更新时间
     */
    public function handle($params)
    {
        $userId        = $params['user_id'];
        $mode          = $params['mode'] ? : 'cart';
        $needInvalid   = isset($params['needInvalid'])? $params['needInvalid'] : true;
        $platform      = $params['platform'] ? : 'pc';
        $requestParams = [
            'user_id' => $userId,
            'mode' => $mode,
            'needInvalid' => $needInvalid,
            'platform' => $platform,
        ];
        $responseData = app::get('topapi')->rpcCall('trade.cart.getBasicCartInfo', $requestParams);
        return ['list'=>$responseData];
    }

    public function returnJson()
    {
        return '';
    }

}



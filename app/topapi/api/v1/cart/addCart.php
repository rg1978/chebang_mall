<?php
/**
 * topapi
 *
 * -- cart.add
 * -- 获取购物车信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_addCart implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '添加购物车数据';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
  //        'user_id'         => ['type'=>'int',     'valid'=>'required',                       'desc'=>'会员id',          'example'=>'3'],
            'quantity'        => ['type'=>'int',     'valid'=>'',                             'desc'=>'商品数量',              'example'=>'3',        'msg'=>''],
            'sku_id'          => ['type'=>'int',     'valid'=>'required_if:obj_type,item',    'desc'=>'货品id',                'example'=>'3',        'msg'=>''],
            'package_sku_ids' => ['type'=>'string',  'valid'=>'required_if:obj_type,package', 'desc'=>'组合促销sku_id',        'example'=>'11,21,45', 'msg'=>''],
            'package_id'      => ['type'=>'integer', 'valid'=>'sometimes|required|integer',   'desc'=>'组合促销id',            'example'=>'3',        'msg'=>''],
            'obj_type'        => ['type'=>'string',  'valid'=>'',                             'desc'=>'对象类型',              'example'=>'item',     'msg'=>''],
            'mode'            => ['type'=>'string',  'valid'=>'in:cart,fastbuy',              'desc'=>'购物车类型,默认是cart', 'example'=>'cart',     'msg'=>''],
        ];
    }

    /**
     * @return int create_time 购物车条目创建时间
     * @return bool is_checked 该条项目是否被选中
     * @return int user_id 会员id
     * @return string user_ident 会员hash
     * @return int shop_id 店铺id
     * @return string obj_type 商品类型
     * @return string obj_ident 商品标示
     * @return int item_id 商品id
     * @return int sku_id 货品id
     * @return string title 商品名称
     * @return string image_default_id 默认图片
     * @return int quantity 商品数量
     * @return int modified_time 最后一次修改时间
     * @return int cart_id 购物车编号
     */
    public function handle($params)
    {
        $params['obj_type'] = $params['obj_type'] ? : 'item';
        $responseData = app::get('topapi')->rpcCall('trade.cart.add', $params);
        return $responseData;
    }

    public function returnJson()
    {
        return '该商品第一次被添加购物车：{"errorcode":0,"msg":"","data":{"created_time":1472028617,"is_checked":null,"user_id":4,"user_ident":"a87ff679a2f3e71d9181a67b7542122c","shop_id":3,"obj_type":"item","obj_ident":"item_380","item_id":86,"sku_id":"380","title":"ONex在线零售平台（C2B）","image_default_id":"http://images.bbc.shopex123.com/images/39/ba/20/390513dbf8ed26a4baa6ef0a77defe8d7896ef0d.png","quantity":1,"modified_time":1472028617,"cart_id":17}}' ;
    }

}



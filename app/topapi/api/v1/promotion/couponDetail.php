<?php
/**
 * topapi
 *
 * -- promotion.shop.coupon.detail
 * -- 获取商家优惠券商品列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_couponDetail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取商家优惠券商品列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'coupon_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'active', 'desc'=>'优惠券id。', 'msg'=>''],
            //分页参数
            'page_no'   => ['type'=>'int','valid'=>'min:1|numeric', 'example'=>'1', 'desc'=>'分页当前页数,默认为1', 'msg'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'10', 'desc'=>'每页数据条数,默认10条', 'msg'=>''],
            // 'orderBy'   => ['type'=>'string','valid'=>'', 'example'=>'', 'desc'=>'商品列表排序，默认 sales_count desc', 'msg'=>''],

            //返回字段
            // 'info_fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'*', 'desc'=>'促销详情需要返回的字段', 'msg'=>''],
            // 'item_fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'*', 'desc'=>'促销商品列表需要返回的字段', 'msg'=>''],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        // 获取优惠券详情基本信息
        $apiFilter = array(
            'page_no' => $params['page_no'] ? (int)$params['page_no'] : 1,
            'page_size' => $params['page_size'] ? (int)$params['page_size'] : 10,
            'orderBy' => $params['order_by'],
            'fields' =>'item_id,shop_id,title,image_default_id,price,promotion_tag',
        );
        $apiFilter['coupon_id'] = $params['coupon_id'];
        $couponItem = app::get('topwap')->rpcCall('promotion.couponitem.list', $apiFilter);
        if(time() > $couponItem['promotionInfo']['canuse_end_time'])
        {
            throw new \LogicException(app::get('topapi')->_('优惠券不存在或者已经失效'));
        }

        $pagedata['info'] = $couponItem['promotionInfo'];
        $pagedata['list'] = $couponItem['list'] ? : (object)[];
        $pagedata['pagers']['total'] = $couponItem['total_found'] ? : 0;

        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"info":{"coupon_id":8,"shop_id":2,"coupon_name":"599元优惠券","coupon_desc":"满4000可用","used_platform":"0","valid_grade":"1,2,3,4,5","limit_money":"4000.000","deduct_money":"599.000","max_gen_quantity":5,"send_couponcode_quantity":1,"use_couponcode_quantity":0,"userlimit_quantity":3,"use_bound":"1","coupon_prefix":"B5SOD","coupon_key":"MDp7czo5Oi","cansend_start_time":1453789380,"cansend_end_time":1580295600,"canuse_start_time":1454083200,"canuse_end_time":1606665600,"created_time":1453778694,"promotion_tag":"优惠券","coupon_status":"agree","reason":null},"list":[{"coupon_id":8,"item_id":1,"shop_id":2,"leaf_cat_id":24,"title":"联想（Lenovo）G40-70M 14.0英寸笔记本电脑","image_default_id":"http://images.bbc.shopex123.com/images/b2/f9/58/be79ce442d995742c6b3ea4869d9e62bce090f73.jpg","price":"4499.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":2,"shop_id":2,"leaf_cat_id":24,"title":"联想（Lenovo）天逸100 15.6英寸笔记本电脑","image_default_id":"http://images.bbc.shopex123.com/images/c1/a1/db/beaa4c65e7650f7e45ceb92efda48e6f96e91178.jpg","price":"3399.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":3,"shop_id":2,"leaf_cat_id":24,"title":"联想（Lenovo）天逸300 15.6英寸笔记本电脑","image_default_id":"http://images.bbc.shopex123.com/images/9c/2a/1e/990f2a9c8ec786ea7b8d5ab462b4cf39e55ccd43.jpg","price":"4200.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":4,"shop_id":2,"leaf_cat_id":24,"title":"联想（Lenovo）U31-70 13.3英寸超薄笔记本","image_default_id":"http://images.bbc.shopex123.com/images/13/3c/79/f04b87dcf4048dceb3e6a6333dce608d4a2fd375.png","price":"4299.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":5,"shop_id":2,"leaf_cat_id":24,"title":"联想 IdeaCentreC560  23英寸 一体电脑","image_default_id":"http://images.bbc.shopex123.com/images/32/4b/c9/3bbac217e58e646913b0ed68f7d58977df8630ac.jpg","price":"3999.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":6,"shop_id":2,"leaf_cat_id":24,"title":"联想 IdeaCentre C560 23英寸一体电脑","image_default_id":"http://images.bbc.shopex123.com/images/51/6e/b2/a3ddf1de62d9832d1da9cc9fe93cdc30698cbdeb.jpg","price":"3859.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":7,"shop_id":2,"leaf_cat_id":24,"title":"联想（Lenovo）H3050 台式电脑","image_default_id":"http://images.bbc.shopex123.com/images/08/ce/21/47e889d755da845827a8e554d1469c94002c4605.jpg","price":"2599.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":8,"shop_id":2,"leaf_cat_id":24,"title":"联想 IdeaCentre C340 20英寸一体电脑","image_default_id":"http://images.bbc.shopex123.com/images/ff/b9/b3/77ca118612ca2600e08cf9eb9e3518fb52c88adc.jpg","price":"4799.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":9,"shop_id":2,"leaf_cat_id":24,"title":"联想（Lenovo） S1801 黑白激光打印机","image_default_id":"http://images.bbc.shopex123.com/images/99/97/ba/4fe2b8fad79194bea63ee13dfbd7af5730da8cab.jpg","price":"599.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0},{"coupon_id":8,"item_id":10,"shop_id":2,"leaf_cat_id":24,"title":"联想（Lenovo）LJ2206W 睿省系列WiFi激光打印","image_default_id":"http://images.bbc.shopex123.com/images/5f/d4/5d/f8be68a4fc6f2c2369e38d93b3dfe64b7a0c44b0.jpg","price":"679.000","promotion_tag":"优惠券","canuse_start_time":1454083200,"canuse_end_time":1606665600,"status":0}],"pagers":{"total":21}}}';
    }

}

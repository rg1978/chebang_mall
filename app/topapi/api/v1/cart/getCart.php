<?php
/**
 * topapi
 *
 * -- cart.get
 * -- 获取购物车信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_cart_getCart implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取购物车信息';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'mode'        => ['type'=>'string',  'valid'=>'in:cart,fastbuy', 'default'=>'cart', 'example'=>'fastbuy', 'desc'=>'购物车类型(立即购买，购物车),默认是cart'],
            'platform'    => ['type'=>'string',  'valid'=>'in:wap,pc',       'default'=>'wap',   'example'=>'true',    'desc'=>'平台,默认是wap'],
        ];
    }

    /**
     */
    public function handle($params)
    {
        $userId        = $params['user_id'];
        $mode          = $params['mode'] ? : 'cart';
        $platform      = $params['platform'] ? : 'wap';
        $requestParams = [
            'user_id' => $userId,
            'mode' => $mode,
            'platform' => $platform,
        ];
        $responseData = app::get('topapi')->rpcCall('trade.cart.getCartInfo', $requestParams);

        $responseData = $this->__genResultForApp($responseData);

        return $responseData;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"cartlist":[{"shop_id":3,"shop_name":"onexbbc自营店（自营店铺）自营店","shop_type":"self","cartCount":{"total_weight":3,"itemnum":3,"total_fee":799,"total_discount":100,"total_coupon_discount":null},"cartitems":[{"discount_price":"100.00","promotion":{"promotion_id":13,"rel_promotion_id":3,"promotion_type":"fulldiscount","promotion_name":"测试满折1","promotion_tag":"满折"},"cartitemlist":[{"cart_id":1,"obj_type":"item","item_id":128,"sku_id":432,"user_id":4,"selected_promotion":"13","cat_id":33,"sub_stock":"0","spec_info":"颜色：浅蓝色、尺码：s","bn":"S56A89E007A740","dlytmpl_id":8,"store":989,"status":"onsale","price":{"discount_price":"100.00","price":"100.000","total_price":"200.00"},"quantity":2,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png","weight":"2.00","activityDetail":{"id":23,"activity_id":5,"shop_id":3,"item_id":128,"cat_id":33,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","item_default_image":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png","price":"224.000","activity_price":"100.000","sales_count":5,"verify_status":"agree","start_time":1472471580,"end_time":1475164800,"activity_tag":"团购","status":1,"activity_info":{"activity_id":5,"activity_name":"测试团购","activity_tag":"团购","activity_desc":"啊蛇岛蝮蛇的","apply_begin_time":1472471220,"apply_end_time":1472471400,"release_time":1472471520,"start_time":1472471580,"end_time":1475164800,"buy_limit":5,"enroll_limit":10,"limit_cat":{"1":"服装鞋包","6":"家用电器","12":"手机数码","14":"电脑、办公","46":"母婴、玩具","66":"个护化妆","254":"食品、生鲜","299":"运动户外","330":"家居用品","363":"营养保健","388":"Shopex"},"shoptype":{"flag":"品牌旗舰店","brand":"品牌专卖店","cat":"类目专营店","self":"运营商自营","store":"多品类通用型"},"discount_min":40,"discount_max":60,"mainpush":0,"slide_images":"/images/d7/06/03/bf360abe2ab285e7b13618c3c1261704517732b4.jpg","enabled":0,"created_time":1472471206,"remind_enabled":0,"remind_way":"email","remind_time":null}},"promotion_type":"fulldiscount","valid":true,"is_checked":1,"promotion_id":13,"promotiontags":[{"promotion_id":12,"promotion_name":"测试满减1"},{"promotion_id":13,"promotion_name":"测试满折1"},{"promotion_id":14,"promotion_name":"测试xy"},{"promotion_id":15,"promotion_name":"测试满减2"}],"gifts":[{"title":"迪士尼童装 儿童长袖T恤 女童时尚百搭T恤 西瓜红","image_default_id":"http://images.bbc.shopex123.com/images/b5/3f/56/5dd258c53a47a3d9b05ea6e7f06649a02b949b30.png_t.png","spec_info":"尺码：105","gift_num":2}]}]},{"discount_price":null,"promotion":{"promotion_id":null,"rel_promotion_id":null,"promotion_type":null,"promotion_name":null,"promotion_tag":null},"cartitemlist":[{"cart_id":7,"obj_type":"item","item_id":23,"sku_id":28,"user_id":4,"selected_promotion":"0","cat_id":33,"sub_stock":"0","spec_info":"颜色：黑色、尺码：s","bn":"S56A5C2E03D298","dlytmpl_id":8,"store":1000,"status":"onsale","price":{"discount_price":0,"price":"599.000","total_price":"599.00"},"quantity":1,"title":"ONLY春季新品修身包臀可拆卸衣摆连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/e3/db/b2/84e4fceb2da83b49091965d7bdcc7f6265fb2dc6.jpg","weight":"1.00","valid":true,"is_checked":1,"promotions":false,"gift":[]}]}]},{"shop_id":2,"shop_name":"Lenovo品牌专卖店专卖店","shop_type":"brand","cartCount":{"total_weight":0,"itemnum":0,"total_fee":0,"total_discount":0,"total_coupon_discount":null},"cartitems":[{"discount_price":"100.00","promotion":{"promotion_id":13,"rel_promotion_id":3,"promotion_type":"fulldiscount","promotion_name":"测试满折1","promotion_tag":"满折"},"cartitemlist":[{"cart_id":1,"obj_type":"item","item_id":128,"sku_id":432,"user_id":4,"selected_promotion":"13","cat_id":33,"sub_stock":"0","spec_info":"颜色：浅蓝色、尺码：s","bn":"S56A89E007A740","dlytmpl_id":8,"store":989,"status":"onsale","price":{"discount_price":"100.00","price":"100.000","total_price":"200.00"},"quantity":2,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png","weight":"2.00","activityDetail":{"id":23,"activity_id":5,"shop_id":3,"item_id":128,"cat_id":33,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","item_default_image":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png","price":"224.000","activity_price":"100.000","sales_count":5,"verify_status":"agree","start_time":1472471580,"end_time":1475164800,"activity_tag":"团购","status":1,"activity_info":{"activity_id":5,"activity_name":"测试团购","activity_tag":"团购","activity_desc":"啊蛇岛蝮蛇的","apply_begin_time":1472471220,"apply_end_time":1472471400,"release_time":1472471520,"start_time":1472471580,"end_time":1475164800,"buy_limit":5,"enroll_limit":10,"limit_cat":{"1":"服装鞋包","6":"家用电器","12":"手机数码","14":"电脑、办公","46":"母婴、玩具","66":"个护化妆","254":"食品、生鲜","299":"运动户外","330":"家居用品","363":"营养保健","388":"Shopex"},"shoptype":{"flag":"品牌旗舰店","brand":"品牌专卖店","cat":"类目专营店","self":"运营商自营","store":"多品类通用型"},"discount_min":40,"discount_max":60,"mainpush":0,"slide_images":"/images/d7/06/03/bf360abe2ab285e7b13618c3c1261704517732b4.jpg","enabled":0,"created_time":1472471206,"remind_enabled":0,"remind_way":"email","remind_time":null}},"promotion_type":"fulldiscount","valid":true,"is_checked":1,"promotion_id":13,"promotiontags":[{"promotion_id":12,"promotion_name":"测试满减1"},{"promotion_id":13,"promotion_name":"测试满折1"},{"promotion_id":14,"promotion_name":"测试xy"},{"promotion_id":15,"promotion_name":"测试满减2"}],"gifts":[{"title":"迪士尼童装 儿童长袖T恤 女童时尚百搭T恤 西瓜红","image_default_id":"http://images.bbc.shopex123.com/images/b5/3f/56/5dd258c53a47a3d9b05ea6e7f06649a02b949b30.png_t.png","spec_info":"尺码：105","gift_num":2}]}]},{"discount_price":null,"promotion":{"promotion_id":null,"rel_promotion_id":null,"promotion_type":null,"promotion_name":null,"promotion_tag":null},"cartitemlist":[{"cart_id":7,"obj_type":"item","item_id":23,"sku_id":28,"user_id":4,"selected_promotion":"0","cat_id":33,"sub_stock":"0","spec_info":"颜色：黑色、尺码：s","bn":"S56A5C2E03D298","dlytmpl_id":8,"store":1000,"status":"onsale","price":{"discount_price":0,"price":"599.000","total_price":"599.00"},"quantity":1,"title":"ONLY春季新品修身包臀可拆卸衣摆连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/e3/db/b2/84e4fceb2da83b49091965d7bdcc7f6265fb2dc6.jpg","weight":"1.00","valid":true,"is_checked":1,"promotions":false,"gift":[]}]},{"discount_price":null,"promotion":{"promotion_id":6,"rel_promotion_id":3,"promotion_type":"fullminus","promotion_name":"满2999立减299","promotion_tag":"满减"},"cartitemlist":[{"cart_id":5,"obj_type":"item","item_id":21,"sku_id":21,"user_id":4,"selected_promotion":"6","cat_id":24,"sub_stock":"0","spec_info":null,"bn":"G56A5B9EF20E02","dlytmpl_id":2,"store":999,"status":"onsale","price":{"discount_price":0,"price":"5399.000","total_price":"5399.00"},"quantity":1,"title":"联想(Lenovo)小新V4000 Bigger版游戏笔记本","image_default_id":"http://images.bbc.shopex123.com/images/31/05/c9/a499c2484196611c228deafbb2464f207d8ee5dd.png","weight":"1.90","valid":true,"is_checked":0,"gift":[],"promotiontags":[{"promotion_id":6,"promotion_name":"满2999立减299"}]}]}]}],"totalCart":{"totalWeight":3,"number":3,"totalPrice":799,"totalAfterDiscount":"699.00","totalDiscount":100}}}';
    }


    protected function __genResultForApp($cart)
    {
        $groupcart = [];
        $groupcart['nocheckedall'] = count($cart['resultCartData']);//统计整个购物车是否权限，为0则代表全选
        foreach($cart['resultCartData'] as $shopId=>$shopInfo)
        {
            $shopcart = [];
            $shopcart['shop_id'] = $shopInfo['shop_id'];
            $shopcart['shop_name'] = $shopInfo['shop_name'];
            $shopcart['shop_type'] = $shopInfo['shop_type'];
            $shopcart['cartCount'] = $shopInfo['cartCount'];
            $shopcart['hasCoupon'] = $this->__hasCoupon($shopInfo['shop_id']);//是否有店铺优惠券
            $shopcart['nocheckedall'] = count($shopInfo['object']);//统计购物车某个店铺是否全选，为0则代表全选
            $groupbypromcarts = [];
            $groupbypromcarts['promotion'] = [];
            foreach($shopInfo['cartByPromotion'] as $prom_id=>$vcartprom)
            {
                $groupbypromcarts['discount_price'] = $vcartprom['discount_price'] ? : 0;//优惠金额
                // 促销分组对应的促销基本信息
                $groupbypromcarts['promotion'] = null;
                if($prom_id>0)
                {
                    $groupbypromcarts['promotion'] = [
                        'promotion_id' => $shopInfo['basicPromotionListInfo'][$prom_id]['promotion_id'],
                        'rel_promotion_id' => $shopInfo['basicPromotionListInfo'][$prom_id]['rel_promotion_id'],
                        'promotion_type' => $shopInfo['basicPromotionListInfo'][$prom_id]['promotion_type'],
                        'promotion_name' => $shopInfo['basicPromotionListInfo'][$prom_id]['promotion_name'],
                        'promotion_tag' => $shopInfo['basicPromotionListInfo'][$prom_id]['promotion_tag'],
                    ];
                }
                // 促销分组内对应的商品信息
                $itemcart = [];
                $groupbypromcarts['cartitemlist'] = [];
                foreach($vcartprom['cart_ids'] as $cartid)
                {
                    $itemcart = $shopInfo['object'][$cartid];
                    $itemcart['image_default_id'] = base_storager::modifier($itemcart['image_default_id'], 't');
                    if($itemcart['is_checked'])
                    {
                        --$shopcart['nocheckedall'];
                    }
                    $itemcart['activityinfo'] = null;
                    if($itemcart['activityDetail'])
                    {
                        $itemcart['activityinfo'] = [
                            'activity_id' => $itemcart['activityDetail']['activity_id'],
                            'price' => $itemcart['activityDetail']['price'],
                            'activity_price' => $itemcart['activityDetail']['activity_price'],
                            'activity_tag' => $itemcart['activityDetail']['activity_tag'],
                        ];
                    }
                    unset($itemcart['activityDetail']);
                    $itemcart['promotiontags'] = null;
                    if($itemcart['promotions'])
                    {
                        foreach ($itemcart['promotions'] as $vitemprom)
                        {
                            $itemcart['promotiontags'][] = ['promotion_id'=>$vitemprom['promotion_id'],'promotion_name'=>$vitemprom['promotion_name'],'promotion_tag'=>$vitemprom['promotion_tag']];
                        }
                        unset($itemcart['promotions']);
                    }
                    $itemcart['gifts'] = null;
                    if($itemcart['gift'])
                    {
                        foreach ($itemcart['gift']['gift_item'] as  $vgift)
                        {
                            $itemcart['gifts'][] = [
                                'title' => $vgift['title'],
                                'image_default_id' => base_storager::modifier($vgift['image_default_id'], 't'),
                                'spec_info' => $vgift['spec_info'],
                                'gift_num' => $vgift['gift_num'],
                            ];
                        }
                    }
                    unset($itemcart['gift']);
                    $groupbypromcarts['cartitemlist'][] = $itemcart;
                }
                $shopcart['promotion_cartitems'][] = $groupbypromcarts;
            }
            if(!$shopcart['nocheckedall'])
            {
                --$groupcart['nocheckedall'];
            }
            $groupcart['cartlist'][] = $shopcart;
        }
        $groupcart['totalCart'] = $cart['totalCart'];

        return $groupcart;
    }

    private function __hasCoupon($shopid)
    {
        // 店铺可领取优惠券
        $params = array(
            'page_no' => 0,
            'page_size' => 1,
            'fields' => '*',
            'shop_id' => $shopid,
            'platform' => 'wap',
            'is_cansend' => 1,
        );
        $couponListData = app::get('topwap')->rpcCall('promotion.coupon.list', $params, 'buyer');
        if($couponListData['count']>0)
        {
            return 1;
        }
        return 0;
    }

}

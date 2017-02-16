<?php
/**
 * topapi
 *
 * -- shop.index
 * -- 获取店铺首页配置信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_shop_indexmodule implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取店铺首页配置信息';

    public $orderSort = array(
        'addtime-l' => 'list_time desc',
        'addtime-s' => 'list_time asc',
        'price-l' => 'price desc',
        'price-s' => 'price asc',
        'sell-l' => 'sold_quantity desc',
        'sell-s' => 'sold_quantity asc',
    );

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'shop_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'1', 'desc'=>'店铺id'],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $shopId = $params['shop_id'];
        $pagedata = $this->__common($shopId);

        //店铺关闭后跳转至关闭页面
        if($pagedata['shopdata']['status'] == "dead")
        {
            return (object)[];
        }

        $pagedata['shopId'] = $shopId;

        // 店铺优惠券信息,
        $params = array(
            'page_no' => 0,
            'page_size' => 10,
            'fields' => 'deduct_money,coupon_name,coupon_id,shop_id',
            'shop_id' => $shopId,
            'platform' => 'wap',
            'is_cansend' => 1,
        );
        $couponListData = app::get('topwap')->rpcCall('promotion.coupon.list', $params, 'buyer');
        $pagedata['homeCouponList']= $couponListData['coupons'];

        // 店铺分类
        $shopcat = app::get('topwap')->rpcCall('shop.cat.get',array('shop_id'=>$shopId,'parent_id'=>0));
        $pagedata['shopcat'] = array_values($shopcat);//自然索引

        //店铺商品展示
        $showItems = shopWidgets::getWapInfo('wapshowitems',$shopId);
        $pagedata['showitems'] = array_values($this->__getShowItems($showItems));
        // $pagedata['collect'] = $this->__CollectInfo($shopId);

        //图片广告配置
        $imageSlider = shopWidgets::getWapInfo('wapimageslider',$shopId);
        $pagedata['imageSlider'] = array_values($imageSlider[0]['params']);

        //自定义广告
        $custom = shopWidgets::getWapInfo('wapcustom', $shopId);
        $custom = $custom[0]['params']['custom'];
        $pagedata['custom'] = $custom;

// echo "<pre>";print_r($pagedata);exit;
        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"shopdata":{"shop_id":3,"shop_name":"onexbbc自营店（自营店铺）","shop_descript":"onexbbc自营体验店","shop_type":"self","status":"active","open_time":1453699800,"qq":"","wangwang":"","shop_logo":"http://images.bbc.shopex123.com/images/e4/64/42/37eff0aeba30e897184f510c248deebd79cff488.png","shop_area":"上海","shop_addr":"桂林路396号","mobile":"13788822222","shopname":"onexbbc自营店（自营店铺）自营店","shoptype":"运营商自营"},"logo_image":{"show_shop_logo":"on","shop_logo":"http://images.bbc.shopex123.com/images/1d/2c/97/c03ed485e7dbbe83622e261d3109b4accfcf28c4.jpg"},"slider":[{"sliderImage":"http://images.bbc.shopex123.com/images/ed/ec/09/0e40b2897438d4cb7ffac23cdad33f45adabfb55.png","link":""},{"sliderImage":"http://images.bbc.shopex123.com/images/42/4c/7e/b884d545bb55a5caf7090c7997c95dfc6a220184.jpg","link":""},{"sliderImage":"http://images.bbc.shopex123.com/images/d0/00/f5/cb432f10fcf9331b21ac852ca3befd173dd2e9e7.jpg","link":""}],"navdata":[{"menu":"Shopex","cat_id":55},{"menu":"女装","cat_id":21},{"menu":"男装","cat_id":52},{"menu":"家电数码","cat_id":29},{"menu":"母婴","cat_id":35}],"itemInfo":[{"widgets_id":43,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"ONLY","item_id":["22","23","24","25","26"],"itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null},{"widgets_id":42,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"迪士尼","item_id":["60","61","63","65","66","72","73","83"],"itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null},{"widgets_id":41,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"ETAM","item_id":["125","126","127","128","129","130"],"itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null},{"widgets_id":40,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"ASICS","item_id":["131","132","133","134"],"itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null}],"shopId":"3","homeCouponList":[{"deduct_money":"15.000","coupon_name":"连衣裙 满100减15","coupon_id":13,"shop_id":3},{"deduct_money":"20.000","coupon_name":"智能设备类 满500减20","coupon_id":12,"shop_id":3},{"deduct_money":"5.000","coupon_name":"满100减5","coupon_id":11,"shop_id":3},{"deduct_money":"100.000","coupon_name":"满1000减100","coupon_id":10,"shop_id":3}],"shopcat":[{"cat_id":21,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"女装","order_sort":0,"modified_time":1472452925,"disabled":0},{"cat_id":52,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"男装","order_sort":2,"modified_time":1472452925,"disabled":0},{"cat_id":29,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"家电数码","order_sort":4,"modified_time":1472452925,"disabled":0},{"cat_id":35,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"母婴","order_sort":7,"modified_time":1472452925,"disabled":0},{"cat_id":55,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"Shopex","order_sort":13,"modified_time":1472452925,"disabled":0}],"showitems":[{"widgets_id":46,"shop_id":3,"widgets_type":"wapshowitems","params":{"tagsname":"女装","item_id":["81","80","79","22","23","24","25","26","123","122","128","130"],"itemlimit":"6","isstart":"1","ordersort":"addtime-l","itemlist":{"list":[{"item_id":130,"title":"etam艾格时尚修身连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/5d/7c/23/aa461ffb145e94922c1036e5aa8edbbb138f1bf6.png","price":"149.000","sold_quantity":0,"promotion":[],"gift":null},{"item_id":128,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png","price":"224.000","sold_quantity":9,"promotion":[{"promotion_id":12,"tag":"X件Y折"},{"promotion_id":13,"tag":"满折"},{"promotion_id":14,"tag":"满减"}],"gift":{"1":{"gift_id":1,"item_id":128,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","shop_id":3,"leaf_cat_id":33,"promotion_tag":"赠品","start_time":1472558100,"end_time":1475236500,"status":1,"gift_item":[{"gift_id":1,"sku_id":273,"item_id":60,"shop_id":3,"gift_num":1,"start_time":1472558100,"end_time":1475236500,"status":1,"title":"迪士尼童装 儿童长袖T恤 女童时尚百搭T恤 西瓜红","spec_info":"尺码：105","image_default_id":"http://images.bbc.shopex123.com/images/b5/3f/56/5dd258c53a47a3d9b05ea6e7f06649a02b949b30.png","bn":"S56A5EB54AC020","store":1000,"freez":0,"realStore":1000,"sub_stock":"0","approve_status":"onsale"}]}}},{"item_id":26,"title":"ONLY春季新品条纹彼得潘领包臀五分袖连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/74/32/fc/414d7236e416eb52ebd17b2e30f8d1f0fb85838c.jpg","price":"249.000","sold_quantity":1,"promotion":[],"gift":null},{"item_id":123,"title":"拼接微透长袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/6d/6e/23/5d1dede8b5575c7f41ee6b865278b437ad9f3173.png","price":"654.000","sold_quantity":3,"promotion":[],"gift":null},{"item_id":122,"title":"夏季新品欧美风圆领无袖女式网眼镂空雪纺连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/48/a6/10/eafdb9c20e9607486befbfac23fcffa16a8a5c35.png","price":"499.000","sold_quantity":1,"promotion":[],"gift":null},{"item_id":23,"title":"ONLY春季新品修身包臀可拆卸衣摆连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/e3/db/b2/84e4fceb2da83b49091965d7bdcc7f6265fb2dc6.jpg","price":"599.000","sold_quantity":7,"promotion":[{"promotion_id":12,"tag":"X件Y折"},{"promotion_id":13,"tag":"满折"},{"promotion_id":14,"tag":"满减"}],"gift":{"1":{"gift_id":1,"item_id":23,"title":"ONLY春季新品修身包臀可拆卸衣摆连衣裙女","shop_id":3,"leaf_cat_id":33,"promotion_tag":"赠品","start_time":1472558100,"end_time":1475236500,"status":1,"gift_item":[{"gift_id":1,"sku_id":273,"item_id":60,"shop_id":3,"gift_num":1,"start_time":1472558100,"end_time":1475236500,"status":1,"title":"迪士尼童装 儿童长袖T恤 女童时尚百搭T恤 西瓜红","spec_info":"尺码：105","image_default_id":"http://images.bbc.shopex123.com/images/b5/3f/56/5dd258c53a47a3d9b05ea6e7f06649a02b949b30.png","bn":"S56A5EB54AC020","store":1000,"freez":0,"realStore":1000,"sub_stock":"0","approve_status":"onsale"}]}}}]}},"modified_time":1453894266,"order_sort":null},{"widgets_id":45,"shop_id":3,"widgets_type":"wapshowitems","params":{"tagsname":"手机","item_id":["31","33","35","39","53","82","84","90","135","136"],"itemlimit":"6","isstart":"1","ordersort":"addtime-l","itemlist":{"list":[{"item_id":136,"title":"Sony/索尼 E5823 Xperia Z5 Compac","image_default_id":"http://images.bbc.shopex123.com/images/1d/2c/97/c03ed485e7dbbe83622e261d3109b4accfcf28c4.jpg","price":"3488.000","sold_quantity":0,"promotion":[]},{"item_id":135,"title":"索尼(SONY) E6883 Xperia Z5尊享版 移动","image_default_id":"http://images.bbc.shopex123.com/images/2e/70/24/85a359d40c6878e553b358337c07392f7ea120f6.png","price":"5699.000","sold_quantity":1,"promotion":[]},{"item_id":90,"title":"华为M2平板电脑8英寸M2-801w/803L LTE通话4","image_default_id":"http://images.bbc.shopex123.com/images/46/86/b6/c5a958bc1b03030eee1bf079d80d05cb7aab0606.png","price":"2289.000","sold_quantity":0,"promotion":[]},{"item_id":84,"title":"LG G4（H818）闪耀金 国际版 移动联通双4G手机 ","image_default_id":"http://images.bbc.shopex123.com/images/0b/eb/fe/ab193c51f360c72b0bd1f1d6bfb0a2d9e0c733c1.png","price":"2699.000","sold_quantity":2,"promotion":[]},{"item_id":82,"title":"魅族 魅蓝metal 32GB 蓝色 ","image_default_id":"http://images.bbc.shopex123.com/images/38/c7/2b/f7abd93395b3b6636a2aea587e885a269224c4b4.png","price":"1199.000","sold_quantity":1,"promotion":[]},{"item_id":33,"title":"微软(Microsoft) Lumia 950 XL DS ","image_default_id":"http://images.bbc.shopex123.com/images/60/a5/fd/e3a973aee1dc3177c475397898eae539f9cdf858.jpg","price":"5499.000","sold_quantity":1,"promotion":[]}]}},"modified_time":1453894181,"order_sort":null},{"widgets_id":44,"shop_id":3,"widgets_type":"wapshowitems","params":{"tagsname":"童装","item_id":["70","68","67","66","65","63","61","60"],"itemlimit":"6","isstart":"1","ordersort":"addtime-l","itemlist":{"list":[{"item_id":70,"title":"纳兰小猪 童装男童加绒加厚卫衣","image_default_id":"http://images.bbc.shopex123.com/images/c1/01/90/a6fe2e777efa177fafe19e31c48dfb1e4cbcb913.png","price":"59.000","sold_quantity":0,"activity":{"activity_id":3,"item_id":70,"activity_tag":"平台活动","price":"59.000","activity_price":"20.000"},"promotion":[]},{"item_id":63,"title":"迪士尼男童女童摇粒绒外套儿童开衫上衣 2016春装","image_default_id":"http://images.bbc.shopex123.com/images/fc/dc/bf/939d8ba149e2f74b5521b1514b6ffc8d179cbf2d.png","price":"89.000","sold_quantity":0,"activity":{"activity_id":3,"item_id":63,"activity_tag":"平台活动","price":"89.000","activity_price":"66.000"},"promotion":[]},{"item_id":65,"title":"迪士尼女童简约百搭翻领打底衫 2015冬季新款 白","image_default_id":"http://images.bbc.shopex123.com/images/c4/cc/d3/b1d3a500709ae3717b8879bcc2b9938864405e54.png","price":"58.000","sold_quantity":0,"activity":{"activity_id":3,"item_id":65,"activity_tag":"平台活动","price":"58.000","activity_price":"42.000"},"promotion":[]},{"item_id":66,"title":"迪士尼 可爱印花百搭时尚短袖T恤 蓝绿","image_default_id":"http://images.bbc.shopex123.com/images/ac/d0/3e/dd9932da2606f6984d348af9a2c8e036ef956fe0.png","price":"39.000","sold_quantity":1,"activity":{"activity_id":3,"item_id":66,"activity_tag":"平台活动","price":"39.000","activity_price":"25.000"},"promotion":[]},{"item_id":67,"title":"纳兰小猪童装男童衬衫加绒加厚中大儿童长袖秋装2015新款衬衣","image_default_id":"http://images.bbc.shopex123.com/images/fc/04/43/c7d244e2c104d6d2f40b9064aab188411099134d.png","price":"50.000","sold_quantity":1,"activity":{"activity_id":3,"item_id":67,"activity_tag":"平台活动","price":"50.000","activity_price":"39.990"},"promotion":[]},{"item_id":68,"title":"纳兰小猪童装男童卫衣加厚冬款 中大儿童加绒套头卫衣","image_default_id":"http://images.bbc.shopex123.com/images/89/65/45/220308767e11239cdd860754a6621536780f46d3.png","price":"59.000","sold_quantity":2,"activity":{"activity_id":3,"item_id":68,"activity_tag":"平台活动","price":"59.000","activity_price":"19.000"},"promotion":[]}]}},"modified_time":1453894164,"order_sort":null}],"imageSlider":[{"sliderImage":"http://images.bbc.shopex123.com/images/aa/27/4d/0c84de7d8550c4e6b4ea459225e3a8a8b65eb1a6.png","link":""},{"sliderImage":"http://images.bbc.shopex123.com/images/ee/e4/86/4dd811fac6071da08bb47f6454071e96d9a1cadd.png","link":""}],"custom":"我是天使你是什么"}}';
    }

    /**
     * 获取店铺模板页面头部共用部分的数据
     *
     * @param int $shopId 店铺ID
     * @return array
     */
    private function __common($shopId)
    {
        $shopId = intval($shopId);
        //店铺信息
        $shopdata = app::get('topwap')->rpcCall('shop.get',array('shop_id'=>$shopId));
        $commonData['shopdata'] = $shopdata;

        //店铺招牌背景色
        $wapslider = shopWidgets::getWapInfo('waplogo',$shopId);
        $commonData['logo_image'] = $wapslider[0]['params'];
        //$commonData['background_image'] = shopWidgets::getWidgetsData('shopsign',$shopId);

        //店铺论播广告
        $wapslider = shopWidgets::getWapInfo('wapslider',$shopId);
        $commonData['slider'] = array_values($wapslider[0]['params']);

        //店铺菜单
        $navData = shopWidgets::getWidgetsData('nav',$shopId);
        $commonData['navdata'] = array_values($navData);
        //标签展示
        $itemList = shopWidgets::getWapInfo('waptags',$shopId);
        $commonData['itemInfo'] = array_values($this->__getItemInfo($itemList));

        return $commonData;
    }

    //获取标签
    private function __getItemInfo($data)
    {
        $sort = unserialize(app::get('topshop')->getConf('wap_decorate.tagSort'));
        foreach ($data as $key => $value)
        {
            if($value['params']['isstart'])
            {
                $itemData[$value['widgets_id']] = $value;
                $itemData[$value['widgets_id']]['order_sort'] = $sort[$value['widgets_id']]['order_sort'];
            }
        }
        $items = $this->array_sort($itemData,'order_sort');

        return $items;
    }

    //获取商品
    private function __getShowItems($data)
    {
        $sort = unserialize(app::get('topshop')->getConf('wap_decorate.showItemSort'));
        foreach ($data as $key => $value)
        {
            if($value['params']['isstart'])
            {
                $itemData[$value['widgets_id']] = $value;
                $params=array('shop_id'=>$value['shop_id'],'use_platform'=>'0');
                $params['orderBy'] = $this->orderSort[$value['params']['ordersort']];
                $params['page_size'] = $value['params']['itemlimit'];
                $params['pages'] = 1;
                $item_id = '';
                foreach ($value['params']['item_id'] as $k => $v)
                {
                    $item_id .= $v.',';
                }
                $params['item_id'] = rtrim($item_id, ",");

                $itemsList = kernel::single('topapi_item_search')->setLimit($params['page_size'])
                    ->search($params)
                    ->setItemsActivetyTag()
                    ->setItemsPromotionTag()
                    ->getData();
                $itemData[$value['widgets_id']]['params']['itemlist'] = $itemsList;
                $itemData[$value['widgets_id']]['order_sort'] = $sort[$value['widgets_id']]['order_sort'];
            }

        }
        $items = $this->array_sort($itemData,'order_sort');
        return $items;
    }
    //排序
    private function array_sort($arr,$keys,$type='asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v)
        {
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc')
        {
            asort($keysvalue);
        }
        else
        {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v)
        {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }


}

<?php
class topwap_ctl_shop extends topwap_controller{

    public $limit = 10;
    public $maxpages = 100;

    public $orderSort = array(
        'addtime-l' => 'list_time desc',
        'addtime-s' => 'list_time asc',
        'price-l' => 'price desc',
        'price-s' => 'price asc',
        'sell-l' => 'sold_quantity desc',
        'sell-s' => 'sold_quantity asc',
    );

    public function __construct()
    {
        parent::__construct();
        $this->setLayoutFlag('shop');
    }

    public function index()
    {
        $shopId = input::get('shop_id');
        $pagedata = $this->__common($shopId);

        //店铺关闭后跳转至关闭页面
        if($pagedata['shopdata']['status'] == "dead")
        {
            return $this->page('topwap/shop/close_shop.html', $pagedata);
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
        $pagedata['shopcat'] = app::get('topwap')->rpcCall('shop.cat.get',array('shop_id'=>$shopId));
        foreach($pagedata['shopcat'] as $shopCatId=>&$row)
        {
            if( $row['children'] )
            {
                $row['cat_id'] = $row['cat_id'].','.implode(',', array_column($row['children'], 'cat_id'));
            }
        }

        //店铺商品展示
        $showItems = shopWidgets::getWapInfo('wapshowitems',$shopId);
        $pagedata['showitems'] = $this->__getShowItems($showItems);
        $pagedata['collect'] = $this->__CollectInfo($shopId);

        //店铺广告图片展示
        $imageSlider = shopWidgets::getWapInfo('wapimageslider',$shopId);
        $pagedata['imageSlider'] = $imageSlider[0]['params'];

        //自定义广告
        $custom = shopWidgets::getWapInfo('wapcustom', $shopId);
        $custom = $custom[0]['params']['custom'];
        $pagedata['custom'] = $custom;
        return $this->page('topwap/shop/index.html', $pagedata);
    }

    /**
     * 获取店铺详情
     *
     * @param int $shopId 店铺ID
     */
    public function shopInfo()
    {
        $shopId = input::get('shop_id');
        $pagedata['shopinfo'] = app::get('topwap')->rpcCall('shop.get',['shop_id'=>$shopId]);
        $pagedata['shopDsrData'] = $this->__getShopDsr($shopId);
        $pagedata['collect'] = $this->__CollectInfo($shopId);

        return $this->page('topwap/shop/shop_info.html',$pagedata);

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
        $commonData['slider'] = $wapslider[0]['params'];
        if( $commonData['slider'])
        {
            $commonData['slider_first_image'] = reset($commonData['slider']);
            $commonData['slider_last_image'] = end($commonData['slider']);
        }

        //店铺菜单
        $navData = shopWidgets::getWidgetsData('nav',$shopId);
        $commonData['navdata'] = $navData;
        //标签展示
        $itemList = shopWidgets::getWapInfo('waptags',$shopId);
        $commonData['itemInfo'] = $this->__getItemInfo($itemList);

        //获取默认图片信息
        $commonData['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');


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

                $itemsList = kernel::single('topwap_item_search')->search($params)
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

    /**
     * 获取店铺评分
     *
     * @param int $shopId 店铺ID
     */
    private function __getShopDsr($shopId)
    {
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = true;
        $dsrData = app::get('topwap')->rpcCall('rate.dsr.get', $params);
        if( !$dsrData )
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',5.0);
            $countDsr['attitude_dsr'] = sprintf('%.1f',5.0);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',5.0);
        }
        else
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',$dsrData['tally_dsr']);
            $countDsr['attitude_dsr'] = sprintf('%.1f',$dsrData['attitude_dsr']);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',$dsrData['delivery_speed_dsr']);
        }
        $shopDsrData['countDsr'] = $countDsr;
        $shopDsrData['catDsrDiff'] = $dsrData['catDsrDiff'];
        return $shopDsrData;
    }

    //排序
    public function array_sort($arr,$keys,$type='asc')
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

    //当前商品收藏和店铺收藏的状态
    private function __CollectInfo($shopId)
    {
        $userId = userAuth::id();
        $collect = unserialize($_COOKIE['collect']);

        if(in_array($shopId, $collect['shop']))
        {
            $pagedata['shopCollect'] = 1;
        }
        else
        {
            $pagedata['shopCollect'] = 0;
        }

        return $pagedata;
    }
}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_shopcenter extends topc_controller
{

    public $limit = 20;
    // 店铺二级域名
    public $subdomain;

    public function __construct($app)
    {
        parent::__construct();
        $this->app = $app;
        $this->setLayoutFlag('shopcenter');
        $shopId = input::get('shop_id');
        if(app::get('site')->getConf('site.subdomain_enabled') && route::currentParameters()['subdomain'])
        {
            // 获取店铺shop_id
            $subdomain = route::currentParameters()['subdomain'];
            $shopId = app::get('topc')->rpcCall('shop.subdomain.getshopid', ['subdomain'=>$subdomain])['shop_id'];
            input::merge(['shop_id'=>$shopId]);
            $this->subdomain = $subdomain;
        }
        if( !$this->__checkShop($shopId) )
        {
            $pagedata['shopid'] = input::get('shop_id');
            $this->page('topc/shop/error.html', $pagedata)->send();
        }
    }

    /**
     * 检查shopId是否存在
     *
     * @param int $shopId 店铺ID
     */
    private function __checkShop($shopId)
    {
        $shopId = intval($shopId);
        if($shopId)
        {
            $shopdata = app::get('topc')->rpcCall('shop.get',array('shop_id'=>$shopId));
            if( empty($shopdata) || $shopdata['status'] == "dead" )
            {
                return false;
            }
            $this->shopData = $shopdata;
            return true;
        }
        return false;
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
        // $shopdata = app::get('topc')->rpcCall('shop.get',array('shop_id'=>$shopId));
        $shopdata = $this->shopData;
        $commonData['shopdata'] = $shopdata;

        //店铺招牌背景色
        $commonData['background_image'] = shopWidgets::getWidgetsData('shopsign',$shopId);

        $commonData['navdata_shop_index_active'] = 'on';
        //店铺菜单
        $navData = shopWidgets::getWidgetsData('nav',$shopId);
        $shopCatId = input::get('shop_cat_id');
        if( $shopCatId )
        {
            foreach( $navData as &$row )
            {
                if( $row['cat_id'] ==  $shopCatId || ( isset($row['children']) && in_array($shopCatId, array_keys($row['children']))) )
                {
                    $row['active'] = true;
                    $commonData['navdata_shop_index_active'] = 'off';
                }
            }
        }

        $commonData['navdata'] = $navData;

        //获取默认图片信息
        $commonData['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');
        if( userAuth::check() )
        {
            $commonData['nologin'] = 1;
        }
        // 获取店铺子域名
        $commonData['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$shopId))['subdomain'];

        return $commonData;
    }

    //店铺首页
    public function index()
    {
        $shopId = input::get('shop_id');
        $pagedata = $this->__common($shopId);

        //店铺自定义区域
        $params = shopWidgets::getWidgetsData('custom',$shopId);
        if($params)
        {
            $pagedata['params'] = $params['custom'];
        }

        //店铺商品
        $items = shopWidgets::getWidgetsData('showitems',$shopId,'0,1');
        if( $items )
        {
            $itemIds = array();
            foreach( $items as $row )
            {
                $itemIds = array_merge($itemIds,array_column($row,'item_id'));
            }

            $activityParams['item_id'] = implode(',',array_unique($itemIds));
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if( $activityItemList )
            {
                $activatyItem = array_bind_key($activityItemList['list'],'item_id');
                foreach( $items as &$row )
                {
                    foreach( $row as &$item )
                    {
                        if( $activatyItem[$item['item_id']] )
                        {
                            $item['activity_tag'] = $activatyItem[$item['item_id']]['activity_tag'];
                            $item['price'] = $activatyItem[$item['item_id']]['activity_price'];
                        }
                    }
                }
            }
        }

        $pagedata['items'] = $items;

        // 店铺优惠券信息,
        $params = array(
            'page_no' => 0,
            'page_size' => 10,
            'fields' => 'deduct_money,coupon_name,coupon_id',
            'shop_id' => $shopId,
            'platform' => 'pc',
            'is_cansend' => 1,
        );
        $couponListData = app::get('topc')->rpcCall('promotion.coupon.list', $params, 'buyer');
        $pagedata['homeCouponList']= $couponListData['coupons'];

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['file'] = "topc/shop/center.html";
        $url = url::action("topc_ctl_shopcenter@index",array('subdomain'=>$this->subdomain,'shop_id'=>$shopId));
        $pagedata['qrCodeData'] = getQrcodeUri($url,80,0);

        return $this->page('topc/shop/index.html', $pagedata);
    }

    public function search()
    {
        $shopId = input::get('shop_id');
        $pagedata = $this->__common($shopId);

        $objLibFilter = kernel::single('topc_item_filter');
        $params = $objLibFilter->decode(input::get());
        $params['shop_id'] = $shopId;
        $params['use_platform'] = '0,1';

        if($params['shop_id'])
        {
            $pagedata['shopCat'] = $shopCat = app::get('topc')->rpcCall('shop.cat.get',array('shop_id'=>$params['shop_id']));
        }

        if($params['shop_cat_id'] && $shopCat[$params['shop_cat_id']] )
        {
            if( $shopCat[$params['shop_cat_id']]['children'] )
            {
                $params['shop_cat_id'] = array_keys($shopCat[$params['shop_cat_id']]['children']);
            }
        }
        $params['shop_cat_id'] = is_array($params['shop_cat_id']) ? implode(',', $params['shop_cat_id']) : $params['shop_cat_id'];

        $searchParams = $params;
        $searchParams['page_no'] = $params['pages'] ? $params['pages'] : 1;
        $searchParams['page_size'] = $this->limit;
        if( !isset($params['orderBy']) )
        {
            $params['orderBy'] =  'sold_quantity desc';
        }
        $searchParams['orderBy'] = $params['orderBy'];
        $searchParams['fields'] = 'item_id,title,image_default_id,price';

        try{
            $itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $itemsList = array();
        }
        //检测是否有参加团购活动
        if($itemsList['list'])
        {
            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['price'] = $value['activity_price'];
                }
            }
        }

        $items = $itemsList['list'];
        $count = $itemsList['total_found'];

        $pagedata['items'] = $items;
        $pagedata['activeFilter'] = $params;

        $tmpFilter = $params;
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        $current = $params['pages'] ? $params['pages'] : 1;
        if($count > 0 ) $total = ceil($count/$this->limit);
        $params['pages'] = time();
        $params['subdomain'] = $pagedata['subdomain'];
        $pagedata['pages'] = array(
            'link' => url::action('topc_ctl_shopcenter@search',$params),
            'current' => $current,
            'total' => $total,
            'token' => $params['pages'],
        );
        $pagedata['file'] = "topc/shop/search.html";

        $url = url::action("topc_ctl_shopcenter@index",array('subdomain'=>$this->subdomain,'shop_id'=>$shopId));
        $pagedata['qrCodeData'] = getQrcodeUri($url,80,0);

        return $this->page('topc/shop/index.html', $pagedata);
    }

    public function shopCouponList()
    {
        $shopId = input::get('shop_id');
        $pagedata = $this->__common($shopId);

       // 店铺优惠券信息,
        $params = array(
            'page_no' => 0,
            'page_size' => 100,
            'fields' => '*',
            'shop_id' => $shopId,
            'platform' => 'pc',
            'is_cansend' => 1,
        );
        $couponListData = app::get('topc')->rpcCall('promotion.coupon.list', $params, 'buyer');
        $pagedata['shopCouponList'] = $couponListData['coupons'];
        $pagedata['file'] = "topc/shop/shopCouponList.html";
        return $this->page('topc/shop/index.html', $pagedata);
    }

    public function getCouponResult()
    {
        $shopId = input::get('shop_id');
        $pagedata = $this->__common($shopId);
        $coupon_id = input::get('coupon_id');
        $validator = validator::make(
            [$coupon_id],
            ['numeric']
        );
        if ($validator->fails())
        {
            return $this->splash('error',null,'领取优惠券参数错误!',true);
        }
        //echo '<pre>';print_r($coupon_id);exit();
        $pagedata['couponInfo'] = app::get('topc')->rpcCall('promotion.coupon.get', array('coupon_id'=>$coupon_id));
        $pagedata['file'] = "topc/shop/couponResult.html";
        return $this->page('topc/shop/index.html', $pagedata);
    }

    public function getCouponCode()
    {
        $apiData['shop_id'] = $shopId = input::get('shop_id');
        $pagedata = $this->__common($shopId);
        $user_id = userAuth::id();
        if(!$user_id)
        {
            $signinUrl =  url::action('topc_ctl_passport@signin');
            return $this->splash('success', $signinUrl, '', true);
        }
        $coupon_id = input::get('coupon_id');
        $validator = validator::make(
            [$coupon_id],
            ['numeric']
        );
        if ($validator->fails())
        {
            return $this->splash('error',null,'领取优惠券参数错误!',true);
        }
        if(!$coupon_id)
        {
            return $this->splash('error', '', '领取优惠券参数错误', true);
        }
        try
        {
            $userInfo = app::get('topc')->rpcCall('user.get.info',array('user_id'=>$user_id),'buyer');
            $apiData = array(
                 'coupon_id' => $coupon_id,
                 'user_id' =>$user_id,
                 'shop_id' =>$shopId,
                 'grade_id' =>$userInfo['grade_id'],
            );
            if(app::get('topc')->rpcCall('user.coupon.getCode', $apiData))
            {
                $url = url::action('topc_ctl_shopcenter@getCouponResult', array('coupon_id'=>$coupon_id, 'shop_id'=>$shopId));
                return $this->splash('success', $url, '领取成功', true);
                // $pagedata['couponInfo'] = app::get('topc')->rpcCall('promotion.coupon.get', array('coupon_id'=>$coupon_id));
                // $pagedata['file'] = "topc/shop/couponResult.html";
                // return $this->page('topc/shop/index.html', $pagedata);
            }
            else
            {
                return $this->splash('error', '', '领取失败', true);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', '', $msg, true);
        }
    }

    // 商家文章
    public function shopArticle()
    {
        $shopId = input::get('shop_id');
        $aid = input::get('aid');
        $preview = input::get('preview', 0);
        if(!$aid)
        {
            kernel::abort(404);
        }

        $pagedata = $this->__common($shopId);

        // 获取文章列表
        $list = $this->__getArticleList($shopId, $preview);
        if(!$list)
        {
            kernel::abort(404);
        }
        // 获取文章详情
        $params['shop_id'] = $shopId;
        $params['article_id'] = $aid;
        $params['fields'] = '*';
        $info = app::get('topc')->rpcCall('syscontent.shop.info.article', $params);
        if(!$info || ($info['pubtime'] > time() && !$preview))
        {
            kernel::abort(404);
        }

        $pagedata['article'] = $info;
        $pagedata['aid'] = $aid;
        $pagedata['shop_id'] = $shopId;
        $pagedata['list'] = $list['list'];
        $pagedata['nodes'] = $list['nodes'];
        $pagedata['file'] = 'topc/shop/article.html';

        return $this->page('topc/shop/index.html', $pagedata);
    }

    private function __getArticleList($shopId, $preview)
    {
        $params['shop_id'] = $shopId;
        $params['platform'] = '0,1';
        if(!$preview)
        {
            $params['is_pub'] = true;
        }

        $params['fields'] = 'article_id,title,node_id';
        $result = app::get('topc')->rpcCall('syscontent.shop.list.article', $params);

        if(!$result['list'])
        {
            return [];
        }
        // 获取所有的分类
        $params = [];
        $params['shop_id'] = $shopId;
        $params['node_ids'] = implode(',', array_column($result['list'], 'node_id'));
        $params['fields'] = 'node_id,node_name';

        $nodes = app::get('topc')->rpcCall('syscontent.shop.list.article.node', $params);
        $nodes = array_bind_key($nodes['list'], 'node_id');
        $tmp = [];
        foreach ($result['list'] as $val)
        {
            if(array_key_exists($val['node_id'], $nodes))
            {
                $tmp[$val['node_id']][] = $val;
            }
            else
            {
                $tmp[0][] = $val;
            }
        }

        $result['list'] = $tmp;
        $result['nodes'] = $nodes;
        return $result;
    }

}



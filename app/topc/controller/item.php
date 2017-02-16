<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
use Endroid\QrCode\QrCode;
class topc_ctl_item extends topc_controller {

    private function __setting()
    {
        $setting = kernel::single('image_data_image')->getImageSetting('item');
        return $setting;
    }

    public function index()
    {
        $this->setLayoutFlag('product'); //设置模板

        $itemId = intval(input::get('item_id'));
        if( empty($itemId) )
        {
            return redirect::action('topc_ctl_default@index');
        }

        if( userAuth::check() )
        {
            $pagedata['nologin'] = 1;
        }

        $pagedata['user_id'] = userAuth::id();

        $params['item_id'] = $itemId;
        $params['fields'] = "*,item_desc.pc_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
        $detailData = app::get('topc')->rpcCall('item.get',$params);
        if(!$detailData)
        {
            $pagedata['error'] = "很抱歉，您查看的宝贝不存在，可能已下架或者被转移";
            return $this->page('topc/items/error.html', $pagedata);
        }

        // 获取是否免邮的信息
        $dlytmplInfo = app::get('topc')->rpcCall('logistics.dlytmpl.get', ['template_id'=>$detailData['dlytmpl_id'], 'fields'=>'is_free']);

        if($dlytmplInfo)
        {
            $pagedata['freePostage'] = $dlytmplInfo['is_free'];
        }

        if(count($detailData['sku']) == 1)
        {
            $detailData['default_sku_id'] = array_keys($detailData['sku'])[0];
        }

        $detailData['valid'] = $this->__checkItemValid($detailData);

        //判断此商品发布的平台，如果是wap端，跳转至wap链接
        if($detailData['use_platform'] == 2 )
        {
            redirect::action('topwap_ctl_item_detail@index',array('item_id'=>$itemId))->send();exit;
        }

        //相册图片
        if( $detailData['list_image'] )
        {
            $detailData['list_image'] = explode(',',$detailData['list_image']);
        }

        //获取商品的促销信息
        $promotionInfo = app::get('topc')->rpcCall('item.promotion.get', array('item_id'=>$itemId));
        if($promotionInfo)
        {
            foreach($promotionInfo as $vp)
            {
                $basicPromotionInfo = app::get('topc')->rpcCall('promotion.promotion.get', array('promotion_id'=>$vp['promotion_id'], 'platform'=>'pc'));
                if($basicPromotionInfo['valid']===true)
                {
                    $pagedata['promotionDetail'][] = $basicPromotionInfo;
                }
            }
        }
        $pagedata['promotion_count'] = count($pagedata['promotionDetail']);

        //获取赠品促销信息
        $giftDetail = app::get('topc')->rpcCall('promotion.gift.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');
        if($giftDetail)
        {
            $pagedata['giftDetail'] = $giftDetail;
        }

        // 活动促销(如名字叫团购)
        $activityDetail = app::get('topc')->rpcCall('promotion.activity.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');
        if($activityDetail)
        {
            $pagedata['activityDetail'] = $activityDetail;
        }

        $detailData['spec'] = $this->__getSpec($detailData['spec_desc'], $detailData['sku']);

        $detailData['qrCodeData'] = $this->__qrCode($itemId);

        $pagedata['item'] = $detailData;

        //获取商品详情页左侧店铺分类信息
        $pagedata['shopCat'] = app::get('topc')->rpcCall('shop.cat.get',array('shop_id'=>$pagedata['item']['shop_id']));

        //获取该商品的店铺信息
        $pagedata['shop'] = app::get('topc')->rpcCall('shop.get',array('shop_id'=>$pagedata['item']['shop_id']));

        //获取该商品店铺的DSR信息
        $pagedata['shopDsrData'] = $this->__getShopDsr($pagedata['item']['shop_id']);

        $pagedata['next_page'] = url::action("topc_ctl_item@index",array('item_id'=>$itemId));

        if( $pagedata['user_id'] )
        {
            //获取该用户的最近购买记录
            $pagedata['buyerList'] = app::get('topc')->rpcCall('trade.user.buyerList',array('user_id'=>$pagedata['user_id']));
            $pagedata['buyerList'] = array_bind_key($pagedata['buyerList'],'item_id');
        }

        //商品浏览历史
        $pagedata['itemBrowserHistory'] = $this->itemBrowserHistoryGet();

        //获取商品属性
        $props = '';
        if($detailData['spec_desc'])
        {
            foreach ($detailData['spec_desc'] as $k=>$v)
            {
                $str = '';
                $str = $detailData['spec']['specName'][$k] . '有';
                $mstr = '';
                foreach ($v as $spec_value_id => $spec_value)
                {
                    $mstr .= $spec_value['spec_value'] . '，';
                }

                $str = $str . $mstr;

                $props .= $str;
            }

            $props = rtrim($props, '，');
        }

        // 面包屑数据
        $cat = app::get('topc')->rpcCall('category.cat.get.data',array('cat_id'=>intval($detailData['cat_id'])));
        $brand = app::get('topc')->rpcCall('category.brand.get.info',array('brand_id'=>$detailData['brand_id'],'fields'=>'brand_id,brand_name'));
        $pagedata['breadcrumb'] = [
            ['url'=>url::action('topc_ctl_topics@index',array('cat_id'=>$cat['lv1']['cat_id'])),'title'=>$cat['lv1']['cat_name']],
            ['url'=>'','title'=>$cat['lv2']['cat_name']],
            ['url'=>url::action('topc_ctl_list@index',array('cat_id'=>$cat['lv3']['cat_id'])),'title'=>$cat['lv3']['cat_name']],
            ['url'=>url::action('topc_ctl_list@index',array('cat_id'=>$cat['lv3']['cat_id'],'brand_id'=>$brand['brand_id'])),'title'=>$brand['brand_name']],
            ['url'=>'','title'=>$detailData['title']],
        ];

        //设置此页面的seo
        $seoData = array(
            'item_title' => $detailData['title'],
            'shop_name' =>$pagedata['shop']['shop_name'],
            'item_brand' => $brand['brand_name'],
            'item_bn' => $detailData['bn'],
            'item_cat' =>$cat['lv3']['cat_name'],
            'sub_title' =>$detailData['sub_title'],
            'sub_props' => $props
        );
        seo::set('topc.item.detail',$seoData);

        // 获取当前平台设置的货币符号
        $cur_symbol = app::get('topc')->rpcCall('currency.get.symbol',array());
        $pagedata['cur_symbol'] = $cur_symbol;

        //商品收藏和店铺收藏情况
        $pagedata['collect'] = $this->__CollectInfo(input::get('item_id'),$pagedata['shop']['shop_id']);
        $pagedata['imurl'] = app::get('topc')->res_full_url . '/im.png';

        // 默认图片
        $pagedata['image_default_id'] = $this->__setting();

        // 获取店铺子域名
        $pagedata['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$pagedata['shop']['shop_id']))['subdomain'];

        return $this->page('topc/items/index.html', $pagedata);
    }

    public function itemBrowserHistoryGet()
    {
        //获取商品浏览历史纪录
        if( userAuth::check() )
        {
            $browserHistoryItemIds = app::get('topc')->rpcCall('user.browserHistory.get',array('user_id'=>userAuth::id()));
            $itemIds = $browserHistoryItemIds['itemIds'];
        }
        else
        {
            $itemIds = $_COOKIE['itemBrowserHistory'] ? $_COOKIE['itemBrowserHistory'] : null;
            if( $itemIds )
            {
                $itemIds = array_reverse(explode(',',$itemIds));
            }
        }

        if( !$itemIds ) return array();

        $itemIds = implode(',',$itemIds);
        $fields = 'item_id,title,price,image_default_id';
        $data = app::get('topc')->rpcCall('item.list.get',['item_id'=>$itemIds, 'fields'=>$fields]);
        foreach( explode(',',$itemIds) as $id  )
        {
            $return[$id] = $data[$id];
        }
        return $return;
    }

    function getSpecSku()
    {
        $params['item_id'] = input::get('item_id');
        $params['fields'] = "spec_desc,sku";
        $detailData = app::get('topc')->rpcCall('item.get',$params);
        $spec = $this->__getSpec($detailData['spec_desc'], $detailData['sku']);
        return json_encode($spec['specSku']);
    }

    //当前商品收藏和店铺收藏的状态
    private function __CollectInfo($itemId,$shopId)
    {
        $userId = userAuth::id();
        $collect = unserialize($_COOKIE['collect']);
        if(in_array($itemId, $collect['item']))
        {
            $pagedata['itemCollect'] = 1;
        }
        else
        {
            $pagedata['itemCollect'] = 0;
        }
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
    //商品列表页加入购物车
    public function miniSpec()
    {
        $itemId = intval(input::get('item_id'));
        if( empty($itemId) )
        {
            return redirect::action('topc_ctl_default@index');
        }

        if( userAuth::check() )
        {
            $pagedata['nologin'] = 1;
        }
        $pagedata['user_id'] = userAuth::id();
        $params['item_id'] = $itemId;
        $params['fields'] = "*,item_desc.pc_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
        $detailData = app::get('topc')->rpcCall('item.get',$params);
        if(!$detailData)
        {
            $pagedata['error'] = "很抱歉，您查看的宝贝不存在，可能已下架或者被转移";
            return $this->page('topc/items/error.html', $pagedata);
        }
        if(count($detailData['sku']) == 1)
        {
            $detailData['default_sku_id'] = array_keys($detailData['sku'])[0];
        }

        $detailData['valid'] = $this->__checkItemValid($detailData);

        //判断此商品发布的平台，如果是wap端，跳转至wap链接
        if($detailData['use_platform'] == 2 )
        {
            redirect::action('topwap_ctl_item_detail@index',array('item_id'=>$itemId))->send();exit;
        }

        $detailData['spec'] = $this->__getSpec($detailData['spec_desc'], $detailData['sku']);
        $detailData['qrCodeData'] = $this->__qrCode($itemId);
        $pagedata['item'] = $detailData;
        return view::make('topc/list/spec_dialog.html', $pagedata);
    }

    // 获取商品的组合促销商品
    public function getPackage()
    {
        $params['item_id'] = intval (input::get('item_id'));
        $pagedata = app::get('topc')->rpcCall('promotion.package.getPackageItemsByItemId', $params);
        $basicPackageTag = [];
        foreach($pagedata['data'] as &$v)
        {
            $oldTotalPrice = 0;
            $packageTotalPrice = 0;
            foreach($v['items'] as $v1)
            {
                $oldTotalPrice += $v1['price'];
                $packageTotalPrice = ecmath::number_plus(array($v1['package_price'],$packageTotalPrice));
            }
            $v['old_total_price'] = $oldTotalPrice;
            $v['package_total_price'] = $packageTotalPrice;
            $v['cut_total_price'] = ecmath::number_minus(array($v['old_total_price'], $v['package_total_price']));
            $basicPackageTag[] = array('name'=>$v['package_name'], 'package_id'=>$v['package_id']);
        }
        if(!$pagedata)return;
        $pagedata['package_tags'] = $basicPackageTag;
        return view::make('topc/items/package.html', $pagedata);
    }

    public function getPackageItemSpec()
    {

        $inputdata = input::get();
        $validator = validator::make([$inputdata['package_id']],['numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'数据格式错误！',true);
        }
        $params = array(
            'page_no' => 1,
            'page_size' => 10,
            'fields' =>'item_id,shop_id,title,image_default_id,price,package_price',
            'package_id' => $inputdata['package_id'],
        );
        $packageItemList = app::get('topc')->rpcCall('promotion.packageitem.list', $params);
        $itemsIds = array_column($packageItemList['list'],'item_id');
        $pagedata['packageInfo'] = $packageItemList['promotionInfo'];
        $packageItemList = array_bind_key($packageItemList['list'], 'item_id');
        if(!$itemsIds)return;
        $detailData = array();
        $specSkuData = array();
        $pagedata['valid'] = true;
        foreach($itemsIds as $itemId)
        {
            $params = array(
                'item_id'=>$itemId,
                'fields' => "item_id,item_store,image_default_id,price,title,spec_desc,sku,spec_index,item_status",
            );
            $detailData[$itemId] = app::get('topc')->rpcCall('item.get',$params);
            if(!$detailData[$itemId])
            {
                $detailData[$itemId] = $packageItemList[$itemId];
                $detailData[$itemId]['valid'] = false;
                $detailData[$itemId]['is_delete'] = true;
            }
            else
            {
                $detailData[$itemId]['valid'] = $this->__checkItemValid($detailData[$itemId]);
            }

            if( $pagedata['valid'] && ! $detailData[$itemId]['valid'] )
            {
                $pagedata['valid'] = false;
            }
            $detailData[$itemId]['spec'] = $this->__getSpec($detailData[$itemId]['spec_desc'], $detailData[$itemId]['sku']);
            $detailData[$itemId]['package_price'] = $packageItemList[$itemId]['package_price'];
            $specSkuData[$itemId] = $detailData[$itemId]['spec']['specSku'];
            if(count($detailData[$itemId]['sku']) == 1)
            {
                $detailData[$itemId]['default_sku_id'] = array_keys($detailData[$itemId]['sku'])[0];
            }
        }
        $pagedata['item'] = $detailData;
        $pagedata['image_default_id'] = $this->__setting();
        $pagedata['package_id'] = $inputdata['package_id'];
        $pagedata['total_package_price'] = ecmath::number_plus(array_column($packageItemList,'package_price'));
        $pagedata['total_old_price'] = ecmath::number_plus(array_column($packageItemList,'price'));
        foreach($specSkuData as &$v1)
        {
            foreach($v1 as &$v2)
            {
                $v2['package_price'] = $packageItemList[$v2['item_id']]['package_price'];
            }
        }
        $pagedata['specSkuData'] = $specSkuData; //用于规格选择
        return view::make('topc/items/package_spec.html', $pagedata);
    }

    private function __qrCode($itemId)
    {
        $url = url::action("topwap_ctl_item_detail@index",array('item_id'=>$itemId));
        return getQrcodeUri($url, 80, 10);
    }

    private function __checkItemValid($itemsInfo)
    {
        if( empty($itemsInfo) ) return false;

        //违规商品
        if( $itemsInfo['violation'] == 1 ) return false;

        //未启商品
        if( $itemsInfo['disabled'] == 1 ) return false;

        //未上架商品
        if($itemsInfo['approve_status'] != 'onsale') return false;

        return true;
    }

    private function __getShopDsr($shopId)
    {
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = true;
        $dsrData = app::get('topc')->rpcCall('rate.dsr.get', $params);
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

    private function __getRateResultCount($itemId)
    {
        $countRateData = app::get('topc')->rpcCall('item.get.count',array('item_id'=>$itemId,'fields'=>'item_id,rate_count,rate_good_count,rate_neutral_count,rate_bad_count'));
        if( !$countRateData[$itemId]['rate_count'] )
        {
            $countRate['good']['num'] = 0;
            $countRate['good']['percentage'] = '0%';
            $countRate['neutral']['num'] = 0;
            $countRate['neutral']['percentage'] = '0%';
            $countRate['bad']['num'] = 0;
            $countRate['bad']['percentage'] = '0%';
            return $countRate;
        }
        $countRate['good']['num'] = $countRateData[$itemId]['rate_good_count'];
        $countRate['good']['percentage'] = sprintf('%.2f',$countRateData[$itemId]['rate_good_count']/$countRateData[$itemId]['rate_count'])*100 .'%';
        $countRate['neutral']['num'] = $countRateData[$itemId]['rate_neutral_count'];
        $countRate['neutral']['percentage'] = sprintf('%.2f',$countRateData[$itemId]['rate_neutral_count']/$countRateData[$itemId]['rate_count'])*100 .'%';
        $countRate['bad']['num'] = $countRateData[$itemId]['rate_bad_count'];
        $countRate['bad']['percentage'] = sprintf('%.2f',$countRateData[$itemId]['rate_bad_count']/$countRateData[$itemId]['rate_count'])*100 .'%';
        $countRate['total'] = $countRateData[$itemId]['rate_count'];
        return $countRate;
    }

    public function getItemRate()
    {
        $itemId = input::get('item_id');
        if( empty($itemId) ) return '';

        $pagedata =  $this->__searchRate($itemId);
        $pagedata['countRate'] = $this->__getRateResultCount($itemId);
        $pagedata['item_id'] = $itemId;

        return view::make('topc/items/rate.html', $pagedata);
    }

    public function getItemRateList()
    {
        $itemId = input::get('item_id');

        $pagedata =  $this->__searchRate($itemId);

        return view::make('topc/items/rate/list.html',$pagedata);
    }

    private function __searchRate($itemId)
    {
        $current = input::get('pages',1);
        $params = ['item_id'=>$itemId,'page_no'=>$current,'page_size'=>10,'fields'=>'*,append'];

        if( in_array(input::get('result'), ['good','bad', 'neutral']) )
        {
            $params['result'] = input::get('result');
            $pagedata['result'] = $params['result'];
        }
        else
        {
            $pagedata['result'] = 'all';
        }
        if( input::get('content') )
        {
            $params['is_content'] = true;
        }
        if( input::get('picture') )
        {
            $params['is_pic'] = true;
        }

        $data = app::get('topc')->rpcCall('rate.list.get', $params);
        foreach($data['trade_rates'] as $k=>$row )
        {
            if($row['rate_pic'])
            {
                $data['trade_rates'][$k]['rate_pic'] = explode(",",$row['rate_pic']);
            }

            if( $row['append']['append_rate_pic'] )
            {
                $data['trade_rates'][$k]['append']['append_rate_pic'] = explode(',', $row['append']['append_rate_pic']);
            }

            $userId[] = $row['user_id'];
        }
        $pagedata['rate']= $data['trade_rates'];
        if( $userId )
        {
            $pagedata['userName'] = app::get('topc')->rpcCall('user.get.account.name',array('user_id'=>$userId));
        }

        //处理翻页数据
        $filter = input::get();
        $filter['pages'] = time();
        if($data['total_results']>0) $total = ceil($data['total_results']/10);
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_item@getItemRateList',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        return $pagedata;
    }

    private function __getSpec($spec, $sku)
    {
        if( empty($spec) ) return array();

        foreach( $sku as $row )
        {
            $key = implode('_',$row['spec_desc']['spec_value_id']);

            if( $key )
            {
                $result['specSku'][$key]['sku_id'] = $row['sku_id'];
                $result['specSku'][$key]['item_id'] = $row['item_id'];
                $result['specSku'][$key]['price'] = $row['price'];
                $result['specSku'][$key]['mkt_price'] = $row['mkt_price'];
                $result['specSku'][$key]['store'] = $row['realStore'];
                if( $row['status'] == 'delete')
                {
                    $result['specSku'][$key]['valid'] = false;
                }
                else
                {
                    $result['specSku'][$key]['valid'] = true;
                }

                $specIds = array_flip($row['spec_desc']['spec_value_id']);
                $specInfo = explode('、',$row['spec_info']);
                foreach( $specInfo  as $info)
                {
                    $id = each($specIds)['value'];
                    $result['specName'][$id] = explode('：',$info)[0];
                }
            }
        }
        return $result;
    }

    //以下为商品咨询
    public function getItemConsultation()
    {

        $itemId = intval(input::get('item_id')) ;

        if( empty($itemId) ) return '';

        $pagedata =  $this->__searchConsultation($itemId);
        $pagedata['item_id'] = $itemId;
        $pagedata['user_id'] = userAuth::id();

        return view::make('topc/items/consultation.html', $pagedata);
    }

    public function getItemConsultationList()
    {
        $itemId = intval(input::get('item_id'));

        $pagedata =  $this->__searchConsultation($itemId);
        return view::make('topc/items/consultation/list.html',$pagedata);
    }

    private function __searchConsultation($itemId)
    {
        $current = intval(input::get('pages',1)) ;
        $params = ['item_id'=>intval($itemId),'page_no'=>$current,'page_size'=>10,'fields'=>'*'];

        if( in_array(input::get('result'), ['item','store_delivery', 'payment','invoice']) )
        {
            $params['type'] = input::get('result');
            $pagedata['result'] = 'all';
        }
        else
        {
            $pagedata['result'] = 'all';
        }

        $data = app::get('topc')->rpcCall('rate.gask.list', $params);

        $pagedata['gask']= $data['lists'];
        $pagedata['count'] = app::get('topc')->rpcCall('rate.gask.count', $params);

        //处理翻页数据
        $filter = input::get();
        $pagedata['filter'] = $filter;
        $filter['pages'] = time();
        if($data['total_results']>0) $total = ceil($data['total_results']/10);
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_item@getItemConsultationList',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        return $pagedata;
    }

    /**
     * @brief 商品咨询提交
     *
     * @return
     */
    public function commitConsultation()
    {
        $post = input::get();
        $params['item_id'] = $post['item_id'];
        $params['content'] = $post['content'];
        $params['type'] = $post['type'];
        $params['is_anonymity'] = $post['is_anonymity'] ? $post['is_anonymity'] : 0;
        $params['ip'] = request::getClientIp();

       if(userAuth::id())
        {
            $params['user_name'] = userAuth::getLoginName();
            $params['user_id'] = userAuth::id();
        }
        else
        {
            if(!$post['contack'])
            {
                return $this->splash('error',$url,"由于您没有登录，咨询请填写联系方式",true);
            }
            $params['contack'] = $post['contack'];
            $params['user_name'] = '游客';
            $params['user_id'] = "0";
        }

        try{
            if($params['contack'])
            {
                //$type = kernel::single('pam_tools')->checkLoginNameType($params['contack']);
                $type = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$params['contack']),'buyer');
                if($type == "login_account")
                {
                    throw new \LogicException('请填写正确的联系方式(手机号或邮箱)');
                }
            }

            $params = utils::_filter_input($params);
            $result = app::get('topc')->rpcCall('rate.gask.create',$params);
            $msg = '咨询提交失败';
        }
        catch(\Exception $e)
        {
            $result = false;
            $msg = $e->getMessage();
        }

        if( !$result )
        {
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topc_ctl_item@index',array('item_id'=>$postdata['item_id']));

        $msg = '咨询提交成功,请耐心等待商家审核、回复';
        return $this->splash('success',$url,$msg,true);
    }

    public function setBrowserHistory()
    {
        $itemId = input::get('item_id');
        if( !is_numeric($itemId) ) return 'item_id必须为numeric';

        if( userAuth::check() )
        {
            //已登录
            app::get('topc')->rpcCall('user.browserHistory.set', ['user_id'=>userAuth::id(), 'itemIds'=>$itemId]);
        }
        else
        {
            //未登录
            $data = $_COOKIE['itemBrowserHistory'] ? explode(',',$_COOKIE['itemBrowserHistory']) : null;
            $key = array_search($itemId, $data);

            if( $data && is_numeric($key) )
            {
                unset($data[$key]);
            }

            $data[] = $itemId;
            if( count($data) >= 6 ) array_shift($data);

            $itemIdStr = implode(',',$data);

            $path = $path ?: kernel::base_url().'/';
            $life = 315360000;
            $expire = $expire === false ? time() + $life : $expire;
            setcookie('itemBrowserHistory', $itemIdStr, $expire, $path);
        }

        return $itemIdStr;
    }
}


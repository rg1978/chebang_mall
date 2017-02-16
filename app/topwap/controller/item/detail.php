<?php

/**
 * detail.php 商品详情
 *
 * @author
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topwap_ctl_item_detail extends topwap_controller {

    public function index()
    {

        $itemId = intval(input::get('item_id'));
        if( empty($itemId) )
        {
            return redirect::action('topwap_ctl_default@index');
        }
        if( userAuth::check() )
        {
            $pagedata['nologin'] = 1;
        }
        $pagedata['image_default_id'] = $this->__setting();
        $params['item_id'] = $itemId;
        $params['fields'] = "*,item_desc.wap_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
        $detailData = app::get('topwap')->rpcCall('item.get',$params);
        if(!$detailData)
        {
            $pagedata['error'] = "商品过期不存在";
            return $this->page('topwap/item/detail/error.html', $pagedata);
        }

        if(count($detailData['sku']) == 1)
        {
            $detailData['default_sku_id'] = array_keys($detailData['sku'])[0];
        }

        $detailData['valid'] = $this->__checkItemValid($detailData);
        if($detailData['use_platform'] != 2 && $detailData['use_platform'] != 0)
        {
            $pagedata['error'] = "该商品仅适用于电脑端";
            return $this->page('topwap/item/detail/error.html', $pagedata);
        }

        //相册图片
        if( $detailData['list_image'] )
        {
            $detailData['list_image'] = explode(',',$detailData['list_image']);
            $detailData['list_image_first'] = reset($detailData['list_image']);
            $detailData['list_image_last'] = end($detailData['list_image']);
        }

        $dlytmplParams['template_id'] = $detailData['dlytmpl_id'];
        $dlytmplParams['fields'] = 'is_free';
        //获取是否免邮的信息
        $dlytmplInfo = app::get('topwap')->rpcCall('logistics.dlytmpl.get',$dlytmplParams);
        if($dlytmplInfo)
        {
            $pagedata['freeConf'] = $dlytmplInfo['is_free'];
        }
        //获取商品的促销信息
        $promotionInfo = app::get('topwap')->rpcCall('item.promotion.get', array('item_id'=>$itemId));
        if($promotionInfo)
        {

            foreach($promotionInfo as $vp)
            {
                $basicPromotionInfo = app::get('topwap')->rpcCall('promotion.promotion.get', array('promotion_id'=>$vp['promotion_id'], 'platform'=>'wap'));

                if($basicPromotionInfo['valid']===true)
                {
                    $pagedata['promotionDetail'][$vp['promotion_id']] = $basicPromotionInfo;
                    $pagedata['promotionTag'][$basicPromotionInfo['promotion_type']] = $basicPromotionInfo;
                }
            }
        }
        $pagedata['promotion_count'] = count($pagedata['promotionDetail']);

        //获取赠品促销信息
        $giftDetail = app::get('topwap')->rpcCall('promotion.gift.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');
        if($giftDetail)
        {
            $pagedata['giftDetail'] = $giftDetail;
        }

        // 活动促销(如名字叫团购)
        $activityDetail = app::get('topwap')->rpcCall('promotion.activity.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');
        if($activityDetail)
        {
            $pagedata['activityDetail'] = $activityDetail;
        }

        $detailData['spec'] = $this->__getSpec($detailData['spec_desc'], $detailData['sku']);

        $pagedata['item'] = $detailData;

        $pagedata['shop'] = app::get('topwap')->rpcCall('shop.get',array('shop_id'=>$pagedata['item']['shop_id']));
        $pagedata['next_page'] = url::action("topwap_ctl_item_detail@index",array('item_id'=>$itemId));
        //商品收藏和店铺收藏情况
        $pagedata['collect'] = $this->__CollectInfo(input::get('item_id'),$pagedata['shop']['shop_id']);
        // 获取评价
        $pagedata['countRate'] = $this->__getRateResultCount($detailData);
        // 获取当前平台设置的货币符号和精度
        $cur_symbol = app::get('topwap')->rpcCall('currency.get.symbol',array());
        $pagedata['cur_symbol'] = $cur_symbol;
        $this->setLayoutFlag('product');
        return $this->page('topwap/item/detail/index.html', $pagedata);
    }

    //商品描述
    public function itemPic()
    {
        $itemId = intval(input::get('item_id'));
        if( empty($itemId) )
        {
            return redirect::action('topwap_ctl_default@index');
        }

        $pagedata['image_default_id'] = $this->__setting();
        $params['item_id'] = $itemId;
        $params['fields'] = "*,item_desc.wap_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
        $detailData = app::get('topwap')->rpcCall('item.get',$params);
        $pagedata['title'] = "商品描述";
        $pagedata['itemPic'] = $detailData;
        // 商品自然属性
        $pagedata['itemParamshtml'] = view::make('topwap/item/detail/itemparams.html', $detailData)->render();
        // 商品备注
        $pagedata['itemremarkhtml'] = view::make('topwap/item/detail/itemremark.html',$detailData)->render();

        return $this->page('topwap/item/detail/itempic.html', $pagedata);
    }

    // 商品评价
    public function getItemRate()
    {
        $itemId = intval(input::get('item_id'));
        if( empty($itemId) ) return '';

        $pagedata =  $this->__searchRate($itemId);
        $pagedata['item_id'] = $itemId;
        $pagedata['title'] = app::get('topwap')->_('商品评价');

        return $this->page('topwap/item/detail/itemrate.html', $pagedata);
    }

    // 获取评价列表
    public function getItemRateList()
    {
        try {
            $itemId = intval(input::get('item_id'));
            if( empty($itemId) ) return '';
            $pagedata=$this->__searchRate($itemId);
            $data['pages'] = $pagedata['pages'];
            $data['total'] = $pagedata['total']; // 总页数
            $data['rate_type'] = $pagedata['rate']['result'];
            $data['success'] = true;

            $data['html'] = view::make('topwap/item/detail/itemrate_list.html',$pagedata)->render();
            if(intval($pagedata['total']) <=0)
            {
               $data['html'] = view::make('topwap/empty/rate.html')->render();
            }

        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage(), true);
        }
        return response::json($data);
    }

    public function viewNotifyItem()
    {
        $pagedata = input::get();
        $pagedata['title'] = app::get('topwap')->_('到货通知');

        return $this->page('topwap/item/detail/shipment.html', $pagedata);
    }
    // 到货通知
    public function userNotifyItem()
    {
        try
        {
            $postdata = $this->__checkdata(input::get());
            $params['shop_id'] = $postdata['shop_id'];
            $params['item_id'] = $postdata['item_id'];
            $params['sku_id'] = $postdata['sku_id'];
            $params['email'] = $postdata['email'];
            $result = app::get('topwap')->rpcCall('user.notifyitem',$params);
        }
        catch (Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg);
        }
        $url = url::action('topwap_ctl_item_detail@index', ['item_id'=>$postdata['item_id']]);

        if( $result['sendstatus'] == 'ready' )
        {
            $msg = app::get('topwap')->_('您已经填过该商品的到货通知');
        }
        else
        {
            $msg = app::get('topwap')->_('预订成功');
        }
        return $this->splash('success', $url, $msg);
    }

    private function __checkdata($data)
    {
        $validator = validator::make(
                ['shop_id' => $data['shop_id'] , 'item_id' => $data['item_id'],'sku_id' => $data['sku_id'],'email' => $data['email']],
                ['shop_id' => 'required'       , 'item_id' => 'required',     'sku_id' => 'required', 'email' => 'required|email'],
                ['shop_id' => '店铺id不能为空！' , 'item_id' => '商品id不能为空！','sku_id' => '货品id不能为空！','email' => '邮件不能为空！|邮件格式不正确!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new Exception( $error[0] );
            }
        }
        return $data;
    }

    // 获取评论百分比
    private function __getRateResultCount($detailData)
    {
        if( !$detailData['rate_count'] )
        {
            $countRate['good']['num'] = 0;
            $countRate['good']['percentage'] = '0%';
            $countRate['neutral']['num'] = 0;
            $countRate['neutral']['percentage'] = '0%';
            $countRate['bad']['num'] = 0;
            $countRate['bad']['percentage'] = '0%';
            return $countRate;
        }
        $countRate['good']['num'] = $detailData['rate_good_count'];
        $countRate['good']['percentage'] = sprintf('%.2f',$detailData['rate_good_count']/$detailData['rate_count'])*100 .'%';
        $countRate['neutral']['num'] = $detailData['rate_neutral_count'];
        $countRate['neutral']['percentage'] = sprintf('%.2f',$detailData['rate_neutral_count']/$detailData['rate_count'])*100 .'%';
        $countRate['bad']['num'] = $detailData['rate_bad_count'];
        $countRate['bad']['percentage'] = sprintf('%.2f',$detailData['rate_bad_count']/$detailData['rate_count'])*100 .'%';
        $countRate['total'] = $detailData['rate_count'];
        return $countRate;
    }

    private function __searchRate($itemId)
    {
        $rate_type_arr = ['1'=>'good','2'=>'neutral','3'=>'bad'];
        $current = input::get('pages',1);
        $rate_type = input::get('rate_type');
        $pagedata['rate_type_group'] = $rate_type;
        $limit = 10;
        $params = ['item_id'=>$itemId,'page_no'=>intval($current),'page_size'=>intval($limit),'fields'=>'*,append'];
        if( $rate_type == '4'  )
        {
            $params['is_pic'] = true;
            $pagedata['query_type'] = 'pic';
        }
        else
        {
            $pagedata['query_type'] = 'content';
        }

        if($rate_type)
        {
            $params['result'] = $rate_type_arr[$rate_type];
            $pagedata['rate_type'] = $rate_type_arr[$rate_type];
        }
        $data = app::get('topwap')->rpcCall('rate.list.get', $params);

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
            $pagedata['userName'] = app::get('topwap')->rpcCall('user.get.account.name',array('user_id'=>$userId),'buyer');
        }

        //处理翻页数据
        if($data['total_results']>0) $total = ceil($data['total_results']/$limit);
        $current = $total < $current ? $total : $current;

        $pagedata['pages'] = $current;
        $pagedata['total'] = $total;

        return $pagedata;
    }


    private function __setting()
    {
        $setting = kernel::single('image_data_image')->getImageSetting('item');
        return $setting;
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

    private function __checkItemValid($itemsInfo)
    {
        if( empty($itemsInfo) ) return false;

        //违规商品
        if( $itemsInfo['violation'] == 1 ) return false;

        //未启商品
        if( $itemsInfo['disabled'] == 1 ) return false;

        //未上架商品
        if($itemsInfo['approve_status'] != 'onsale') return false;

        //库存小于或者等于0的时候，为无效商品
        //if($itemsInfo['realStore'] <= 0 ) return false;

        return true;
    }


}

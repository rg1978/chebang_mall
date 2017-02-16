<?php
/**
 * topapi
 *
 * -- item.detail
 * -- 获取商品详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_item_itemDetail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取商品详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'item_id' => ['type'=>'int',      'valid'=>'required|numeric|min:1','example'=>'1',                     'desc'=>'商品id。必须是正整数',             'msg'=>'商品id必须为正整数'],
            'fields' => ['type'=>'field_list','valid'=>'',                      'example'=>'title,item_store.store','desc'=>'要获取的商品字段集。多个字段用“,”分隔','msg'=>''],
        ];
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $itemId = intval($params['item_id']);

        $params['item_id'] = $itemId;
        $default_fields = "*,item_count,item_store,item_status,sku,item_nature,spec_index";
        $fields = $params['fields'] ? : $default_fields;
        $filter = [
            'item_id' => $itemId,
            'fields' => $fields,
        ];
        $detailData = app::get('topapi')->rpcCall('item.get',$filter);
        if(!$detailData)
        {
            return [];
        }
        unset($detailData['list_image']);

        if(count($detailData['sku']) == 1)
        {
            $detailData['default_sku_id'] = array_keys($detailData['sku'])[0];
        }

        $detailData['valid'] = $this->__checkItemValid($detailData);

        $dlytmplParams['template_id'] = $detailData['dlytmpl_id'];
        $dlytmplParams['fields'] = 'is_free';
        //获取是否免邮的信息
        $dlytmplInfo = app::get('topapi')->rpcCall('logistics.dlytmpl.get',$dlytmplParams);
        if($dlytmplInfo)
        {
            $pagedata['freeConf'] = $dlytmplInfo['is_free'];
        }
        //获取商品的促销信息
        $promotionInfo = app::get('topapi')->rpcCall('item.promotion.get', array('item_id'=>$itemId));
        if($promotionInfo)
        {
            $pagedata['promotionTag'] = [];
            foreach($promotionInfo as $vp)
            {
                $basicPromotionInfo = app::get('topapi')->rpcCall('promotion.promotion.get', array('promotion_id'=>$vp['promotion_id'], 'platform'=>'wap'));

                if($basicPromotionInfo['valid']===true)
                {
                    $pagedata['promotionTag'][$basicPromotionInfo['promotion_type']][] = [
                        'promotion_id' => $basicPromotionInfo['promotion_id'],
                        'rel_promotion_id' => $basicPromotionInfo['rel_promotion_id'],
                        'promotion_name' => $basicPromotionInfo['promotion_name'],
                        'promotion_tag' => $basicPromotionInfo['promotion_tag'],
                    ];
                }
            }
        }

        //获取赠品促销信息
        $giftDetail = app::get('topapi')->rpcCall('promotion.gift.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');
        if($giftDetail)
        {
            foreach ($giftDetail['gift_item'] as $gv)
            {
                $pagedata['giftTag']['list'][] = [
                    'title' => $gv['title'],
                    'spec_info' => $gv['spec_info'],
                    'gift_num' => $gv['gift_num'],
                    'image_default_id' => base_storager::modifier($gv['image_default_id'], 't'),
                ];
            }
        }

        // 活动促销(如名字叫团购)
        $activityDetail = app::get('topapi')->rpcCall('promotion.activity.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');
        if($activityDetail)
        {
            $pagedata['activityTag'] = [
                'activity_id'=>$activityDetail['activity_id'],
                'activity_tag'=>$activityDetail['activity_tag'],
                'activity_price'=>$activityDetail['activity_price'],
                'price'=>$activityDetail['price'],
            ];
        }

        // 格式化规格信息
        if($detailData['spec_desc'])
        {
            $detailData['spec'] = $this->__getSpec($detailData['spec_desc'], $detailData['sku']);
            unset($detailData['spec_desc']);
            unset($detailData['sku']);
        }

        $pagedata['item'] = $detailData;

        $pagedata['shop'] = app::get('topapi')->rpcCall('shop.get',array('shop_id'=>$pagedata['item']['shop_id'],'fields'=>'shop_id,shop_name,shop_descript,shop_logo'));
        //商品收藏和店铺收藏情况
        $pagedata['collect'] = $this->__CollectInfo($itemId,$pagedata['shop']['shop_id']);

        // 获取当前平台设置的货币符号和精度
        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $pagedata['cur_symbol'] = $cur_symbol;

        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"freeConf":"0","item":{"item_id":130,"shop_id":3,"cat_id":33,"brand_id":73,"shop_cat_id":",22,","title":"etam艾格时尚修身连衣裙","sub_title":"","bn":"G56A89E984F796","price":"149.000","cost_price":"0.000","mkt_price":"0.000","show_mkt_price":0,"weight":"1.000","unit":"件","image_default_id":"http://images.bbc.shopex123.com/images/5d/7c/23/aa461ffb145e94922c1036e5aa8edbbb138f1bf6.png","order_sort":1,"modified_time":1472202077,"has_discount":0,"is_virtual":0,"is_timing":0,"violation":0,"is_selfshop":1,"nospec":0,"props_name":null,"params":false,"sub_stock":"0","outer_id":null,"is_offline":0,"barcode":"","disabled":0,"use_platform":"0","dlytmpl_id":8,"approve_status":"onsale","reason":null,"list_time":1472202080,"delist_time":null,"sold_quantity":0,"rate_count":0,"rate_good_count":0,"rate_neutral_count":0,"rate_bad_count":0,"view_count":0,"buy_count":0,"images":["http://images.bbc.shopex123.com/images/5d/7c/23/aa461ffb145e94922c1036e5aa8edbbb138f1bf6.png","http://images.bbc.shopex123.com/images/0e/a9/d6/25d3985eeb7d566fafdd5a174c6ef31e42007b9a.png"],"brand_name":"艾格","brand_alias":"","brand_logo":"http://images.bbc.shopex123.com/images/54/85/e3/82b512cdf75ee4c6716921159960bd72b5c82793.jpg","store":6000,"freez":0,"realStore":6000,"natureProps":[{"prop_id":3,"prop_name":"面料","prop_value_id":25,"prop_value":"棉"},{"prop_id":6,"prop_name":"领型","prop_value_id":38,"prop_value":"圆领"}],"valid":true,"spec":{"specSku":{"1_19":{"sku_id":439,"item_id":130,"price":"149.000","store":1000,"valid":true},"1_20":{"sku_id":440,"item_id":130,"price":"149.000","store":1000,"valid":true},"1_21":{"sku_id":441,"item_id":130,"price":"149.000","store":1000,"valid":true},"1_22":{"sku_id":442,"item_id":130,"price":"149.000","store":1000,"valid":true},"1_23":{"sku_id":443,"item_id":130,"price":"149.000","store":1000,"valid":true},"1_24":{"sku_id":444,"item_id":130,"price":"149.000","store":1000,"valid":true}},"specs":[{"spec_id":1,"spec_name":"颜色","spec_values":[{"spec_value_id":"1","spec_value":"白色","spec_image":"http://localhost/bbc/public/images/86/40/90/c3da8760fb3d1206b941df295ca329fa3aa48e1f.jpg"}]},{"spec_id":2,"spec_name":"尺码","spec_values":[{"spec_value_id":"19","spec_value":"s"},{"spec_value_id":"20","spec_value":"m"},{"spec_value_id":"21","spec_value":"l"},{"spec_value_id":"22","spec_value":"xl"},{"spec_value_id":"23","spec_value":"xxl"},{"spec_value_id":"24","spec_value":"xxxl"}]}]}},"shop":{"shop_id":3,"shop_name":"onexbbc自营店（自营店铺）","shop_descript":"onexbbc自营体验店","shop_logo":"http://images.bbc.shopex123.com/images/e4/64/42/37eff0aeba30e897184f510c248deebd79cff488.png","shop_type":"self","shopname":"onexbbc自营店（自营店铺）自营店","shoptype":"运营商自营"},"collect":{"itemCollect":0,"shopCollect":0},"cur_symbol":{"sign":"￥","decimals":2}}}';
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
        if( empty($spec) ) return (object)[];

        foreach( $sku as $row )
        {
            $key = implode('_',$row['spec_desc']['spec_value_id']);

            if( $key )
            {
                $result['specSku'][$key]['sku_id'] = $row['sku_id'];
                $result['specSku'][$key]['item_id'] = $row['item_id'];
                $result['specSku'][$key]['price'] = $row['price'];
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
                    $spceName[$id] = explode('：',$info)[0];
                }
            }
        }

        foreach ($spec as $spec_id => $spec_value_row)
        {
            $specs = [
                'spec_id'=>$spec_id,
                'spec_name'=>$spceName[$spec_id],
            ];

            foreach($spec_value_row as $v)
            {
                $spec_value = [];
                $spec_value['spec_value_id'] = $v['spec_value_id'];
                $spec_value['spec_value'] = $v['spec_value'];
                if($v['spec_image'])
                {
                    $spec_value['spec_image'] = base_storager::modifier($v['spec_image'], 't');
                }
                $specs['spec_values'][] = $spec_value;
            }
            $result['specs'][] = $specs;
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

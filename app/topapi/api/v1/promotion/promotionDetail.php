<?php
/**
 * topapi
 *
 * -- promotion.shop.cartpromotion.detail
 * -- 获取商家促销详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_promotionDetail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取商家促销详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'promotion_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'active', 'desc'=>'促销id。购物车内唯一选择的促销，包括满减，满折，XY折', 'msg'=>''],
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
        // 获取促销详情基本信息
        $promotionInfo = app::get('topapi')->rpcCall('promotion.promotion.get', array('promotion_id'=>$params['promotion_id']));
        if($promotionInfo['valid'])
        {
            $pagedata = $this->__commonPromotionItemList($params, $promotionInfo);
        }
        else
        {
            throw new \LogicException(app::get('topapi')->_('促销不存在或者已经失效'));
        }

        return $pagedata;
    }

    /**
     * 返回促销关联的商品页面
     * @param  array $filter 获取促销关联商品所需的，分页
     * @param  array $promotionInfo 对应促销的促销id，促销类型
     * @return mixed 返回促销关联商品列表等信息
     */
    private function __commonPromotionItemList($params, $promotionInfo)
    {
        $apiFilter = array(
            'page_no' => $params['page_no'] ? (int)$params['page_no'] : 1,
            'page_size' => $params['page_size'] ? (int)$params['page_size'] : 10,
            'orderBy' => $params['order_by'],
            'fields' =>'item_id,shop_id,title,image_default_id,price,promotion_tag',
        );
        //获取促销商品列表
        $promotionItem = $this->__promotionItemList($promotionInfo, $apiFilter);

        $pagedata['info'] = $promotionItem['promotionInfo'];
        $pagedata['info']['promotion_type'] = $promotionInfo['promotion_type'];

        $pagedata['list'] = $promotionItem['list'] ? : (object)[];
        $pagedata['pagers']['total'] = $promotionItem['total_found'] ? : 0;

        return $pagedata;
    }

    //获取促销的类型以及商品数据
    private function __promotionItemList($promotionInfo,$params)
    {
        switch ($promotionInfo['promotion_type'])
        {
            case 'fullminus':
                $params['fullminus_id'] = $promotionInfo['rel_promotion_id'];
                $promotionInfo = app::get('topwap')->rpcCall('promotion.fullminusitem.list', $params);
                break;
            case 'fulldiscount':
                $params['fulldiscount_id'] = $promotionInfo['rel_promotion_id'];
                $promotionInfo = app::get('topwap')->rpcCall('promotion.fulldiscountitem.list', $params);
                break;
            case 'xydiscount':
                $params['xydiscount_id'] = $promotionInfo['rel_promotion_id'];
                $promotionInfo = app::get('topwap')->rpcCall('promotion.xydiscountitem.list', $params);
                break;
        }

        return $promotionInfo;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"info":{"fulldiscount_id":2,"shop_id":2,"fulldiscount_name":"满2000九折优惠","fulldiscount_desc":"满2000元给予9折优惠，所有会员都可参加，可参加次数为5次。","used_platform":"0","use_bound":"1","valid_grade":"1,2,3,4,5","condition_value":"2000|90","join_limit":5,"free_postage":0,"created_time":1453778083,"start_time":1453806000,"end_time":1597316400,"promotion_tag":"满折","fulldiscount_status":"agree","reason":null,"promotion_type":"fulldiscount"},"list":[{"item_id":5,"shop_id":2,"title":"联想 IdeaCentreC560  23英寸 一体电脑","image_default_id":"http://images.bbc.shopex123.com/images/32/4b/c9/3bbac217e58e646913b0ed68f7d58977df8630ac.jpg","price":"3999.000","promotion_tag":"满折"},{"item_id":6,"shop_id":2,"title":"联想 IdeaCentre C560 23英寸一体电脑","image_default_id":"http://images.bbc.shopex123.com/images/51/6e/b2/a3ddf1de62d9832d1da9cc9fe93cdc30698cbdeb.jpg","price":"3859.000","promotion_tag":"满折"},{"item_id":7,"shop_id":2,"title":"联想（Lenovo）H3050 台式电脑","image_default_id":"http://images.bbc.shopex123.com/images/08/ce/21/47e889d755da845827a8e554d1469c94002c4605.jpg","price":"2599.000","promotion_tag":"满折"},{"item_id":8,"shop_id":2,"title":"联想 IdeaCentre C340 20英寸一体电脑","image_default_id":"http://images.bbc.shopex123.com/images/ff/b9/b3/77ca118612ca2600e08cf9eb9e3518fb52c88adc.jpg","price":"4799.000","promotion_tag":"满折"}],"pagers":{"total":4}}}';
    }

}

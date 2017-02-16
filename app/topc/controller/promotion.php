<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_promotion extends topc_controller {

    /**
     * 通用的获取促销关联的商品，主要是购物车促销，如满减，满折，xy折
     * @return mixed 返回促销关联的商品信息，分页信息等
     */
    public function getPromotionItem()
    {
        $filter = input::get();
        $filter['action'] = 'topc_ctl_promotion@getPromotionItem';
        $promotionInfo = app::get('topc')->rpcCall('promotion.promotion.get', array('promotion_id'=>$filter['promotion_id']));
        if($promotionInfo['valid'])
        {
            return $this->__commonPromotionItemList($filter, $promotionInfo);
        }
        else
        {
            return kernel::abort(404);
        }
    }

    /**
     * 返回促销关联的商品页面
     * @param  array $filter 获取促销关联商品所需的，分页
     * @param  array $promotionInfo 对应促销的促销id，促销类型
     * @return mixed 返回促销关联商品列表等信息
     */
    private function __commonPromotionItemList($filter, $promotionInfo)
    {
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 10;
        $params = array(
            'page_no' => $filter['pages'],
            'page_size' => $pageSize,
            'fields' => 'item_id,shop_id,title,image_default_id,price',
        );
        //获取促销商品列表
        $promotionItem = $this->__promotionItemList($promotionInfo, $params);
        $count = $promotionItem['total_found'];
        $promotionItemList = $promotionItem['list'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action($filter['action'], $filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['promotionItemList']= $promotionItemList;
        $pagedata['count'] = $count;
        $pagedata['promotionInfo'] = $promotionItem['promotionInfo'];
        $pagedata['promotiontype'] = $promotionInfo['promotion_type'];

        return $this->page("topc/promotion/promotion.html",$pagedata);
    }

    //获取促销的类型以及商品数据
    private function __promotionItemList($promotionInfo,$params)
    {
        switch ($promotionInfo['promotion_type'])
        {
            case 'fullminus':
                $params['fullminus_id'] = $promotionInfo['rel_promotion_id'];
                $promotionItem = app::get('topc')->rpcCall('promotion.fullminusitem.list', $params);
                $promotionItem['promotionInfo']['condition_value'] = $this->getConditionValue($promotionItem['promotionInfo']['condition_value']);
                break;
            case 'coupon':
                $params['coupon_id'] = $promotionInfo['rel_promotion_id'];
                $promotionItem = app::get('topc')->rpcCall('promotion.couponitem.list', $params);
                // 优惠券生效时间
                if(!$promotionItem || $promotionItem['promotionInfo']['cansend_start_time'] > time())
                {
                    kernel::abort(404);exit;
                }
                break;
            case 'fulldiscount':
                $params['fulldiscount_id'] = $promotionInfo['rel_promotion_id'];
                $promotionItem = app::get('topc')->rpcCall('promotion.fulldiscountitem.list', $params);
                $promotionItem['promotionInfo']['condition_value'] = $this->getConditionValue($promotionItem['promotionInfo']['condition_value']);
                break;
                // 免邮已废弃
            case 'freepostage':
                $params['freepostage_id'] = $promotionInfo['rel_promotion_id'];
                $promotionItem = app::get('topc')->rpcCall('promotion.freepostageitem.list', $params);
                break;
            case 'xydiscount':
                $params['xydiscount_id'] = $promotionInfo['rel_promotion_id'];
                $promotionItem = app::get('topc')->rpcCall('promotion.xydiscountitem.list', $params);
                break;
        }

        return $promotionItem;
    }

    public function getConditionValue($data)
    {
        $conditionValue = explode(",",$data);
        foreach ($conditionValue as $key => $value)
        {
            $fmt[$key] = explode("|",$value);
        }
        return $fmt;
    }

    /**
     * 返回优惠券关联的商品列表
     * @return mixed 返回优惠券关联商品的信息，分页信息等
     */
    public function getCouponItem()
    {
        $filter = input::get();
        $filter['action'] = 'topc_ctl_promotion@getCouponItem';
        $promotionInfo = ['promotion_type'=>'coupon', 'rel_promotion_id'=>$filter['coupon_id']];

        return $this->__commonPromotionItemList($filter, $promotionInfo);
    }
    
    // 促销专题页
    public function ProjectPage($pageId)
    {
        $preview = input::get('preview', 0);
        // 获取促销页详情
        $data = app::get('topc')->rpcCall('promotion.get.page.info', ['page_id'=> $pageId]);
        if(!$data || $data['used_platform'] !='pc')
        {
            kernel::abort(404);
            return false;
        }
        
        
        
        if($preview !=1)
        {
            // 判断活动是否已开始
            if($data['is_display'] == 0 || time() < $data['display_time'])
            {
                kernel::abort(404);
                return false;
            }
        }
        
        // 设置seo
        if($data['page_name'])
        {
            theme::setTitle($param['page_name']);
        }
        if($data['page_desc'])
        {
            theme::setDescription($data['page_desc']);
        }
        
        // 显示页面
        $this->setlayoutflag('promotion');
        $this->setLayout($data['page_tmpl']);
        
        return $this->page();
    }

}


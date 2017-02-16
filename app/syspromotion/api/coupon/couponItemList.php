<?php
/**
 * ShopEx licence
 * - promotion.couponitem.list
 * - 用于获取优惠券关联的商品列表
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-23
 */
final class syspromotion_api_coupon_couponItemList{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取指定优惠券促销商品列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'coupon_id' => ['type'=>'int',        'valid'=>'required', 'title'=>'优惠券ID', 'example'=>'', 'desc'=>'优惠券促销id'],
            'page_no'   => ['type'=>'int',        'valid'=>'',         'title'=>'页码',     'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',        'valid'=>'',         'title'=>'每页条数',  'example'=>'', 'desc'=>'每页数据条数,默认10条'],
            'orderBy'   => ['type'=>'string',     'valid'=>'',         'title'=>'排序',     'example'=>'', 'desc'=>'排序，coupon_id asc'],
            'fields'    => ['type'=>'field_list', 'valid'=>'',         'title'=>'字段列表',  'example'=>'', 'desc'=>'需要的字段'],
        );

        return $return;
    }

    /**
     * 获取优惠券关联的商品列表
     * @desc 用于获取指定优惠券促销商品列表
     * @return array list 关联商品列表
     * @return int list[].coupon_id 优惠券ID
     * @return int list[].item_id 商品ID
     * @return int list[].shop_id 店铺ID
     * @return int list[].leaf_cat_id 商品关联叶子分类ID
     * @return int list[].title 商品标题
     * @return int list[].image_default_id 商品图片链接
     * @return int list[].price 商品价格
     * @return int list[].promotion_tag 促销标签
     * @return int list[].canuse_start_time 起始可使用时间
     * @return int list[].canuse_end_time 截止可使用时间
     * @return int list[].status 促销状态
     * @return int total_found 关联商品总数量
     * @return array promotionInfo 优惠券规则信息
     * @return int promotionInfo.coupon_id 优惠券ID
     * @return int promotionInfo.shop_id 店铺ID
     * @return string promotionInfo.coupon_name 优惠券名称 
     * @return string promotionInfo.coupon_desc 优惠券描述
     * @return string promotionInfo.used_platform 使用平台(0,全场；1,pc；2,wap)
     * @return string promotionInfo.valid_grade 会员级别
     * @return Price promotionInfo.limit_money 满足条件金额
     * @return Price promotionInfo.deduct_money 优惠金额
     * @return int promotionInfo.max_gen_quantity 最大优惠券号码数量
     * @return int promotionInfo.send_couponcode_quantity 已生成的优惠券号码数量
     * @return int promotionInfo.use_couponcode_quantity 已使用的优惠券号码数量
     * @return int promotionInfo.userlimit_quantity 用户总计可领取优惠券数量
     * @return string promotionInfo.use_bound 使用范围
     * @return string promotionInfo.coupon_prefix 优惠券前缀
     * @return string promotionInfo.coupon_key 优惠券生成的key
     * @return timestamp promotionInfo.cansend_start_time 发优惠券开始时间
     * @return timestamp promotionInfo.cansend_end_time 发优惠券结束时间
     * @return timestamp promotionInfo.canuse_start_time 优惠券生效时间
     * @return timestamp promotionInfo.canuse_end_time 优惠券失效时间
     * @return timestamp promotionInfo.created_time 建券时间
     * @return string promotionInfo.promotion_tag 促销标签
     * @return string promotionInfo.coupon_status 促销状态
     */
    public function couponItemList($params)
    {
        $objMdlCouponItem = app::get('syspromotion')->model('coupon_item');
        $objMdlCoupon = app::get('syspromotion')->model('coupon');
        if(!$params['fields'])
        {
        }
            $params['fields'] = '*';
        $couponInfo = $objMdlCoupon->getRow('*',array('coupon_id'=>$params['coupon_id']));
        if($couponInfo['coupon_status']=='agree')
        {
            $filter = array('coupon_id'=>$params['coupon_id']);
            $countTotal = $objMdlCouponItem->count($filter);
            if( $countTotal )
            {
                $pageTotal = ceil($countTotal/$params['page_size']);
                $page =  $params['page_no'] ? $params['page_no'] : 1;
                $limit = $params['page_size'] ? $params['page_size'] : 10;
                $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
                $offset = ($currentPage-1) * $limit;
                $orderBy = $params['orderBy'] ? $params['orderBy'] : 'coupon_id DESC';
                $couponItemList = $objMdlCouponItem->getList($params['fields'], $filter, $offset, $limit, $orderBy);
                $couponItem = array(
                    'list'=>$couponItemList,
                    'total_found'=>$countTotal,
                );
            }
            $couponItem['promotionInfo'] = $couponInfo;
            return $couponItem;
        }
        else
        {
            return false;
        }
    }

}


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家前台会员中心左侧菜单列表
 */

return array(
    array(
        'label' => '我的交易',
        'items' => array(
            array('label' => '我的订单','action'=>'topc_ctl_member_trade@tradeList'),
            array('label' => '取消订单记录','action'=>'topc_ctl_member_trade@canceledTradeList'),
        ),
    ),
    array(
        'label' => '我的服务',
        'items' => array(
            ['label' => '退换货记录','action'=>'topc_ctl_member_aftersales@aftersalesList'],
            ['label' => '订单投诉记录','action'=>'topc_ctl_member_complaints@complaintsList']
        ),
    ),
    array(
        'label' => '我的收藏',
        'items' => array(
            array('label' => '收藏的店铺','action'=>'topc_ctl_member@shopsCollect'),
            array('label' => '收藏的商品','action'=>'topc_ctl_member@itemsCollect'),
        ),
    ),
    array(
        'label' => '我的信息',
        'items' => array(
            array('label' => '我的积分','action'=>'topc_ctl_member_point@point'),
            array('label' => '个人资料','action'=>'topc_ctl_member@seInfoSet'),
            array('label' => '用户名相关设置','action'=>'topc_ctl_member@pwdSet'),
            array('label' => '收货地址管理','action'=>'topc_ctl_member@address'),
            array('label' => '安全中心设置','action'=>'topc_ctl_member@security'),
            array('label' => '我的评价管理','action'=>'topc_ctl_member_rate@index'),
            array('label' => '我的咨询管理','action'=>'topc_ctl_member_consultation@index'),
        ),
    ),
    array(
        'label' => '我的优惠',
        'items' => array(
            array('label' => '我的红包','action'=>'topc_ctl_member_hongbao@index'),
            array('label' => '我的优惠券','action'=>'topc_ctl_member_coupon@couponList'),
            array('label' => '我的预存款','action'=>'topc_ctl_member_deposit@view'),
        ),
    ),
);

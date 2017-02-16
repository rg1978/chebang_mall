<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* @table member_coupon;
*
* @package Schemas
* @version $
* @copyright 2010 ShopEx
* @license Commercial
*/

return  array(
    'columns' => array(
        'id'=> array(
            'type'=>'number',
            'autoincrement' => true,
            'comment' => app::get('sysuser')->_('id'),
        ),
        'name' => array(
            'type' => 'string',
            'length' => '50',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'is_title' => true,
            'label' => app::get('syspromotion')->_('红包名称'),
            'comment' => app::get('syspromotion')->_('红包名称'),
        ),
        'hongbao_id' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('sysuser')->_('红包ID'),
            'comment' => app::get('sysuser')->_('红包ID'),
        ),
        'user_id' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('sysuser')->_('会员ID'),
            'comment' => app::get('sysuser')->_('会员ID'),
        ),
        'obtain_time' => array(
            'type' => 'time',
            'label' => app::get('sysuser')->_('红包获得时间'),
            'comment' => app::get('sysuser')->_('红包获得时间'),
        ),
        'tid' => array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('使用该红包的订单号'),
            'comment' => app::get('sysuser')->_('使用该红包的订单号'),
        ),
        'is_valid' => array(
            'type' => array(
                'used' => app::get('sysuser')->_('已使用'),
                'active' => app::get('sysuser')->_('有效'),
                'expired' => app::get('sysuser')->_('过期'),
            ),
            'default' => 'active',
            'required' => true,
            'editable' => false,
            'label' => app::get('sysuser')->_('会员红包是否当前可用'),
            'comment' => app::get('sysuser')->_('会员红包是否当前可用'),
        ),
        'hongbao_obtain_type' => array(
            'type' => array(
                'aftersales' => app::get('sysuser')->_('售后退还红包'),
                'cancelTrade' => app::get('sysuser')->_('取消订单退还红包'),
                'userGet' => app::get('sysuser')->_('用户主动领取红包'),
                'adminPut' => app::get('sysuser')->_('平台主动发放'),
                'shopPut' => app::get('sysuser')->_('商家主动发放'),
            ),
            'default' => 'userGet',
            'required' => true,
            'label' => app::get('sysuser')->_('获取红包方式'),
            'comment' => app::get('sysuser')->_('获取红包方式'),
        ),
        'obtain_desc' => array(
            'type' => 'string',
             'default' => '用户主动领取',
-            'required' => true,
-            'in_list' => true,
-            'default_in_list' => true,
-            'width' => 110,
-            'label' => app::get('sysuser')->_('领取方式'),
-            'comment' => app::get('sysuser')->_('领取说明，签到赠送，游戏赠送等'),
        ),
        'refund_hongbao_tid' => array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('退还红包的订单'),
            'comment' => app::get('sysuser')->_('退还红包的订单'),
        ),
        'used_platform' => array(
            'type' => array(
                'all' => app::get('sysuser')->_('商家全场可用'),
                'pc' => app::get('sysuser')->_('只能用于pc'),
                'wap' => app::get('sysuser')->_('只能用于wap'),
            ),
            'default' => 'all',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysuser')->_('使用平台'),
            'comment' => app::get('sysuser')->_('使用平台'),
        ),
        'hongbao_type' => array(
            'type' => 'string',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('红包类型'),
            'comment' => app::get('syspromotion')->_('红包类型'),
        ),
        'money' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '50',
            'order' => 14,
            'label' => app::get('sysuser')->_('红包金额'),
            'comment' => app::get('sysuser')->_('红包金额'),
        ),
        'start_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'label' => app::get('sysuser')->_('红包生效时间'),
            'comment' => app::get('sysuser')->_('红包生效时间，冗余字段用于查询'),
        ),
        'end_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'label' => app::get('sysuser')->_('红包失效时间'),
            'comment' => app::get('sysuser')->_('红包失效时间，冗余字段用于查询'),
        ),
    ),
    'primary' => ['id'],
    'index' => array(
        'ind_tid' => ['columns' => ['tid']],
    ),
    'comment' => app::get('sysuser')->_('用户红包表'),
);

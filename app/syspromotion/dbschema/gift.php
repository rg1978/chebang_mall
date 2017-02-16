<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 赠品规则表
return  array(
    'columns' => array(
        'gift_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'width' => 110,
            'label' => app::get('syspromotion')->_('id'),
            'comment' => app::get('syspromotion')->_('赠品方案id'),
        ),
        'shop_id' => array(
            'type' => 'number',
            'required' => true,
            'width' => 150,
            'order' => 5,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('所属商家'),
            'comment' => app::get('syspromotion')->_('所属商家的店铺id'),
        ),
        'created_time' => array(
            'type' => 'time',
            'width' => '100',
            'order' => 9,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('创建时间'),
            'comment' => app::get('syspromotion')->_('创建时间'),
        ),
        'start_time' => array(
            'type' => 'time',
            'width' => '100',
            'in_list' => true,
            'order' => 10,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('开始时间'),
            'comment' => app::get('syspromotion')->_('开始时间'),
        ),
        'end_time' => array(
            'type' => 'time',
            'width' => '100',
            'in_list' => true,
            'order' => 10,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('结束时间'),
            'comment' => app::get('syspromotion')->_('结束时间'),
        ),
        'promotion_tag' => array(
            'type' => 'string',
            'length' => 15,
            'required' => true,
            'default' =>'gift',
            'label' => app::get('syspromotion')->_('促销标签'),
            'comment' => app::get('syspromotion')->_('促销标签'),
        ),
        'gift_name' => array(
            'type' => 'string',
            'order' => 6,
            'required' => true,
             'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('赠品活动名称'),
            'comment' => app::get('syspromotion')->_('赠品活动名称'),
        ),
        'gift_desc' => array(
            //'type' => 'varchar(255)',
            'type' => 'string',
            'required' => true,
            'in_list' => true,
            'width' => 110,
            'label' => app::get('syspromotion')->_('赠品规则描述'),
            'comment' => app::get('syspromotion')->_('赠品规则描述'),
        ),
        'valid_grade' => array(
            //'type' => 'varchar(255)',
            'type' => 'string',
            'default' => '',
            'required' => true,
            'label' => app::get('syspromotion')->_('会员级别集合'),
            'comment' => app::get('syspromotion')->_('会员级别集合'),
        ),
       'condition_type' => array(
            'type' => array(
                'quantity' => app::get('syspromotion')->_('按数量'),
            ),
            'default' => 'quantity',
            'in_list' => true,
            'order' => 7,
            'default_in_list' => true,
            'width' => '50',
            'label' => app::get('syspromotion')->_('送赠品条件标准'),
            'comment' => app::get('syspromotion')->_('送赠品条件标准'),
        ),
        'limit_quantity' => array(
            'type' => 'number',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '50',
            'order' => 8,
            'label' => app::get('syspromotion')->_('满足条件数量'),
            'comment' => app::get('syspromotion')->_('满足条件数量'),
        ),
        'gift_status' => array(
            'type' => array(
                'non-reviewed' => app::get('syspromotion')->_('未审核'),
                'pending' => app::get('syspromotion')->_('待审核'),
                'agree' => app::get('syspromotion')->_('审核通过'),
                'refuse' => app::get('syspromotion')->_('审核拒绝'),
                'cancel' => app::get('syspromotion')->_('已取消'),
            ),
            'default' => 'agree',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 11,
            'width' => 110,
            'label' => app::get('syspromotion')->_('促销状态'),
            'comment' => app::get('syspromotion')->_('促销状态'),
        ),
        'reason'=>array(
            //'type'=>'varchar(500)',
            'type' => 'string',
            'length' => 500,
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('sysshop')->_('不通过原因'),
            'comment' => app::get('sysshop')->_('审核不通过原因'),
            'order' => 12,
        ),
    ),
    'primary' => 'gift_id',
    'index' => array(
        'ind_shop_id' => ['columns' => ['shop_id']],
        'ind_created_time' => ['columns' => ['created_time']],
        'ind_gift_status' => ['columns' => ['gift_status']],
    ),
    'comment' => app::get('syspromotion')->_('优惠券表'),
);


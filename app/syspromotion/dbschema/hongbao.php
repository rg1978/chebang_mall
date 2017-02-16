<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */


//平台红包规则表
return array(
    'columns' => array(
        'hongbao_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'label' => app::get('syspromotion')->_('红包规则id'),
            'comment' => app::get('syspromotion')->_('红包规则id'),
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
        'status' => array(
            'type' => array(
                'pending' => app::get('syspromotion')->_('未开始'),
                'active' => app::get('syspromotion')->_('活动中'),
                'stop' => app::get('syspromotion')->_('终止'),
                'success' => app::get('syspromotion')->_('红包已发完'),
            ),
            'default' => 'pending',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('红包发放状态'),
            'comment' => app::get('syspromotion')->_('红包发放状态'),
        ),
        'used_platform' => array(
            'type' => array(
                'all' => app::get('syspromotion')->_('商家全场可用'),
                'pc' => app::get('syspromotion')->_('只能用于pc'),
                'wap' => app::get('syspromotion')->_('只能用于wap'),
            ),
            'default' => 'all',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('使用平台'),
            'comment' => app::get('syspromotion')->_('使用平台'),
        ),
        'hongbao_type' => array(
            'type' => array(
                'fixed' => app::get('syspromotion')->_('定额红包'),
            ),
            'default' => 'fixed',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('红包类型'),
            'comment' => app::get('syspromotion')->_('红包类型'),
        ),
        'total_money' => array(
            'type' => 'money',
            'required' => true,
            'label' => app::get('syspromotion')->_('红包总金额'),
            'comment' => app::get('syspromotion')->_('可领取的红包总金额'),
        ),
        'total_num' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('syspromotion')->_('红包总数'),
            'comment' => app::get('syspromotion')->_('可领取的红包总数量'),
        ),
        'user_total_money' => array(
            'type' => 'money',
            'required' => true,
            'label' => app::get('syspromotion')->_('用户可领取总金额'),
            'comment' => app::get('syspromotion')->_('用户可领取红包的总金额'),
        ),
        'user_total_num' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('syspromotion')->_('用户可领取总数'),
            'comment' => app::get('syspromotion')->_('用户可领取红包的总数量'),
        ),
        'get_start_time' => array(
            'type' => 'time',
            'default'=> 0,
            'required' => true,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('领取红包起始时间'),
            'comment' => app::get('syspromotion')->_('领取红包起始时间'),
        ),
        'get_end_time' => array(
            'type' => 'time',
            'default'=> 0,
            'required' => true,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('领取红包截止时间'),
            'comment' => app::get('syspromotion')->_('领取红包截止时间'),
        ),
        'use_start_time' => array(
            'type' => 'time',
            'default'=> 0,
            'required' => true,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('使用红包起始时间'),
            'comment' => app::get('syspromotion')->_('使用红包起始时间'),
        ),
        'use_end_time' => array(
            'type' => 'time',
            'default'=> 0,
            'required' => true,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('使用红包截止时间'),
            'comment' => app::get('syspromotion')->_('使用红包截止时间'),
        ),
        'hongbao_list' => array(
            'type' => 'text',
            'required' => true,
            'label' => app::get('syspromotion')->_('生成红包结构'),
            'comment' => app::get('syspromotion')->_('生成红包详细结构'),
        ),
        'created_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => false,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('创建时间'),
            'comment' => app::get('syspromotion')->_('创建时间'),
        ),
    ),
    'primary' => 'hongbao_id',
    'comment' => app::get('syspromotion')->_('平台红包规则表'),
);


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'log_id'=> array(
            'type'=>'number',
            //'pkey'=>true,
            'autoincrement' => true,
            'comment' => app::get('sysuser')->_('日志id'),
        ),
        'type' => array(
            'type' => array(
                'add' => '充值',
                'expense' => '消费',
            ),
            'comment' => app::get('sysuser')->_('日志类型(消费还是充值)'),
        ),
        'user_id' => array (
            'type' => 'table:account@sysuser',
            //'pkey' => true,
            'label' => app::get('sysuser')->_('会员用户id'),
        ),
        'operator' => array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('操作员'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'fee' => array (
            'type' => 'money',
            //'pkey' => true,
            'label' => app::get('sysuser')->_('金额'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'message' => array(
            'type' => 'string',
            //'pkey' => true,
            'label' => app::get('sysuser')->_('变更记录'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'logtime' => array(
            'label' => app::get('sysuser')->_('日志记录时间'),
            'width' => 150,
            'type' => 'time',
            'editable' => false,
            'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),

    ),
    'primary' => 'log_id',
    'index' => array(
        'ind_user_id' => ['columns' => ['user_id']],
    ),
    'comment' => app::get('sysuser')->_('会员预存款记录日志表'),
);

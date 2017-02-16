<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return  array(
	'columns' => array (

        'user_id' => array(
            'type' => 'table:user',
            'pkey' => true,
            'default' => 0,
            'required' => true,
            'editable' => false,
            'comment' => app::get('sysuser')->_('会员ID'),
        ),
    	'checkin_date' =>
     	array(
     		'type' => 'date',
            'pkey' => true,
     		'default' => '1970-01-01',
     		'required' => true,
     		'editable' => false,
     		'comment' => app::get('sysuser')->_('签到日期'),
     		'label' => app::get('sysuser')->_('签到日期'),
     	),
        'checkin_time' =>
        array(
            'type' => 'bigint',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'comment' => app::get('sysuser')->_('签到时间'),
            'label' => app::get('sysuser')->_('签到时间'),
        ),
    ),

    'primary' => ['user_id','checkin_date'],
    'comment' => app::get('sysuser')->_('签到记录表'),
);

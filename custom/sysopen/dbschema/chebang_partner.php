<?php
return array(
    'columns' => array(
        'cp_id' => array(
            'type' => 'number',
            'autoincrement'=>true,
            'required' => true,
        ),
        'cp_name' => array(
            'type' => 'string',
            'required' => true,
            'label' => app::get('sysopen')->_('合作伙伴名称'),
            'comment' => app::get('sysopen')->_('合作伙伴名称'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => 'true',
        ),
        'app_id' => array(
            'type' => 'string',
            'required' => true,
            'label' => app::get('sysopen')->_('合作伙伴appId'),
            'comment' => app::get('sysopen')->_('合作伙伴appId'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'nequal',
            'filtertype' => 'yes',
            'filterdefault' => 'true',
        ),
        'app_secret' => array(
            'type' => 'string',
            'required' => true,
            'label' => app::get('sysopen')->_('合作伙伴appSecret'),
            'comment' => app::get('sysopen')->_('合作伙伴appSecret'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),

        'valid_time'=>array(
            'type' => 'number',
            // 'required' => true,
            'label' => app::get('openapi')->_('token有效期'),
            'comment' => app::get('openapi')->_('token有效期'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,

            'default' =>0,
        ),
//         'sign' => array(
//             'type' => 'varchar(64)',
//             'required' => true,
//             'label' => app::get('sysopen')->_('签名'),
//             'comment' => app::get('sysopen')->_('签名'),
//             'editable' => false,
//         ),
        'addtime' => array(
            'type' => 'time',
            'required' => true,
            'label' => app::get('sysopen')->_('创建时间'),
            'comment' => app::get('sysopen')->_('创建时间'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' =>
        array (
            'ind_app_id' =>
                array (
                    'columns' =>
                        array (
                            0 => 'app_id',
                        ),
                    'prefix' => 'unique',
                ),
        ),
    'primary'=>'cp_id',
    'engine' => 'innodb',
    'comment' => app::get('sysopen')->_('合作伙伴表'),
);
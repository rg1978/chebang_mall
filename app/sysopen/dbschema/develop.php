<?php
//开发者绑定店铺账号，一个开发者账号可以绑定多个店铺账号

return  array(
    'columns'=> array(
        'develop_id' => array(
            'type'=>'number',
            'autoincrement' => true,
            'required' => true,
            'comment' => app::get('sysopen')->_('开发者ID'),
        ),
        'name' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('开发者名称'),
            'comment' => app::get('sysopen')->_('开发者名称'),
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => true,
            'width' => '60',
            'order' => 10,
        ),
        'key' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('key'),
            'comment' => app::get('sysopen')->_('访问api时用的key'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
        'secret' => array(
            'type' => 'string',
            'label' => app::get('sysopen')->_('secret'),
            'comment' => app::get('sysopen')->_('访问api时用的secret'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'contact_type' => array(
            'type' => array(
                'notallowopen' => '禁止接入',
                'applyforopen' => '申请接入',
                'openstandard' => '标准接入',
            ),
            'default' => 'openstandard',
            'required' => true,
            'label' => app::get('sysopen')->_('商户状态'),
            'comment' => app::get('sysopen')->_('商户状态'),
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
    ),
    'primary' => 'develop_id',
    'comment' => app::get('sysopen')->_('开发者账号绑定表'),
);


<?php
return  array(
    'columns'=> array(
        'shop_id'=>array(
            'type'=>'string',
            //'pkey'=>true,
            'autoincrement' => true,
            'required' => true,
            'in_list'=>true,
            'default_in_list'=>true,
            'filtertype' => true,
            'filterdefault' => 'true',
            'label' => app::get('sysshop')->_('商城店铺'),
            'comment' => app::get('sysshop')->_('店铺编号id,0标示平台'),
            'order' => 1,
        ),
        'email'=>array(
            //'type'=>'varchar(200)',
            'type' => 'string',
            'length' => 200,
            'required'=>true,
            'in_list'=>true,
            'default_in_list'=>true,
            'filtertype' => true,
            'filterdefault' => 'true',
            'label' => app::get('sysshop')->_('邮箱'),
            'comment' => app::get('sysshop')->_('邮箱'),
            'order' => 12,
        ),
        'use_im' => array(
            'type' => 'bool',
            'default' => 0,
            'required'=>true,
            'in_list'=>true,
            'default_in_list'=>true,
            'filtertype' => true,
            'filterdefault' => 'true',
            'label' => app::get('sysshop')->_('webcall开关'),
            'comment' => app::get('sysshop')->_('是否开启webcall客服的使用'),
            'order' => 13,
        ),
    ),

    'primary' => 'shop_id',
    'comment' => app::get('sysopen')->_('保存365webcall的账户数据'),
);


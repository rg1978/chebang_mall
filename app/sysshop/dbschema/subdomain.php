<?php
return  array(
    'columns'=>array(
        'shop_id'=>array(
            'type'=>'table:shop@sysshop',
            'required' => true,
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => '店铺id',
            'comment' => app::get('sysshop')->_('店铺编号id'),
        ),
        'subdomain'=>array(
            'type' => 'string',
            'length' => 50,
            'required'=>true,
            'in_list'=>true,
            'default_in_list'=>true,
            'searchtype' => 'has',
            'filtertype' => false,
            'filterdefault' => 'true',
            'label' => app::get('sysshop')->_('二级域名'),
            'comment' => app::get('sysshop')->_('二级域名'),
        ),
        'seller_id'=>array(
            'type'=>'table:account@sysshop',
            'required'=>true,
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('sysshop')->_('店铺管理员'),
            'comment' => app::get('sysshop')->_('提交申请时的用户'),
        ),
        'times'=>array(
            'type' => 'smallint',
            'length' => 2,
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysshop')->_('修改次数'),
            'comment' => app::get('sysshop')->_('修改次数'),
        ),
        'modified_time'=>array(
            'type'=>'time',
            'in_list'=>true,
            'default_in_list'=>true,
            'filtertype' => false,
            'filterdefault' => 'true',
            'label' => app::get('sysshop')->_('最后修改时间'),
            'comment' => app::get('sysshop')->_('最后修改店时间'),
        ),
    ),
    'primary' => 'shop_id',
    'index' => array(
        'ind_subdomin' => ['columns' => ['subdomain']],
    ),
    'comment' => app::get('sysshop')->_('店铺二级域名表'),

);

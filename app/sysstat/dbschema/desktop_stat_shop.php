<?php
return  array(
    'columns'=>array(
        'desktop_statshop_id'=>array(
            'type' => 'bigint',
            'unsigned' => true,
            'autoincrement' => true,
            'required' => true,
            'label' => 'id',
            'comment' => app::get('sysstat')->_('店铺销售排行id 自赠'),
            'order' => 1,
        ),
        'shop_id' => array(
            'type' => 'table:shop@sysshop',
            'required' => true,
            'comment' => app::get('sysstat')->_('店铺id'),
            'order' => 2,
        ),
        'shopname'=>array(
            'type' => 'string',
            'length' => 90,
            'label' => app::get('sysstat')->_('店铺名称'),
            'comment' => app::get('sysstat')->_('店铺名称'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 3,
        ),
        'shopaccountfee'=>array(
            'type' => 'money',
            'default' => 0,
            'label' => app::get('sysstat')->_('销售额'),
            'comment' => app::get('sysstat')->_('销售额'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 4,
        ),
        'shopaccountnum'=>array(
            'type' => 'number',
            'default' => 0,
            'label' => app::get('sysstat')->_('销售量'),
            'comment' => app::get('sysstat')->_('销售量'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 5,
        ),
        'createtime'=>array(
            'type'=>'time',
            'comment' => app::get('sysstat')->_('统计时间'),
            'label' => app::get('sysstat')->_('统计时间'),
            /*'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,*/
            'filtertype' => true,
            'filterdefault' => true,
            'order' => 6,
        ),
    ),
    'primary' => 'desktop_statshop_id',
    'index' => array(
        'ind_createtime' => ['columns' => ['createtime']],
        'ind_shop_id' => ['columns' => ['shop_id']],
    ),
    'comment' => app::get('sysstat')->_('店铺销售排行统计表'),
);

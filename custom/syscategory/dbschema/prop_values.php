<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'prop_value_id' => array(
            'type' => 'number',
            'required' => true,
            //'pkey' => true,
            'autoincrement' => true,
            'editable' => false,
            'comment' => app::get('syscategory')->_('属性值ID'),
        ),
        'prop_id' => array(
            'type' => 'table:props',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('syscategory')->_('属性ID'),
            'comment' => app::get('syscategory')->_('属性ID'),
        ),
        'prop_value' => array(
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'default' => '',
            'required' => true,
            'editable' => false,
            'is_title' => true,            
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('syscategory')->_('属性值'),
            'comment' => app::get('syscategory')->_('属性值'),
        ),
        'prop_image' => array(
            'type' => 'string',
            'default' => '',
            'editable' => false,
            'comment' => app::get('syscategory')->_('属性图片'),
        ),
        'order_sort' => array(
            'type' => 'number',
            'default' => 0,
            'required' => true,
            'comment' => app::get('syscategory')->_('排序'),
        ),
        // 2017-01-17 zhoumin  增加属性值审核字段
		'status'=>array(
            'type'=>array(
                'active'=>'未审核',
                'successful'=>'审核通过',
                'failing'=>'审核驳回',
            ),
            'required'=>true,
            'in_list'=>true,
            'default' => 'active',
            'default_in_list'=>true,
            'label' => app::get('syscategory')->_('申请状态'),
            'comment' => app::get('syscategory')->_('申请状态'),
            'order' => 60,
        ),
        'seller_id'=>array(
            'type'=>'table:account@sysshop',
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('syscategory')->_('商家账号'),
            'comment' => app::get('syscategory')->_('提交申请的账号'),
            'order' => 65,
        ),
        'shop_name'=>array(
            //'type'=>'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'in_list'=>true,
            'default_in_list'=>true,
            'searchtype' => 'has',
            'filtertype' => false,
            'filterdefault' => 'true',
            'label' => app::get('syscategory')->_('店铺名称'),
            'comment' => app::get('syscategory')->_('提交申请的店铺名称'),
            'order' => 70,
        ),
        'refuse_time' => array(
            'type'=>'time',
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('syscategory')->_('拒绝时间'),
            'comment' => app::get('syscategory')->_('拒绝时间'),
            'order' => 75,

        ),
        'agree_time' => array(
            'type'=>'time',
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('syscategory')->_('同意时间'),
            'comment' => app::get('syscategory')->_('同意时间'),
            'order' => 80,
        ),
        'reason'=>array(
            //'type'=>'varchar(500)',
            'type' => 'string',
            'length' => 500,
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('sysshop')->_('拒绝原因'),
            'comment' => app::get('sysshop')->_('审核拒绝原因'),
            'order' => 85,
        ),
        //--------------------------------------------2017-01-17------------
    ),

    'primary' => 'prop_value_id',
    'index' => array(
        'ind_prop_id' => ['columns' => ['prop_id']],
    ),
    'comment' => app::get('syscategory')->_('属性值表'),
);

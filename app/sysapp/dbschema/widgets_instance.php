<?php
/**
* ShopEx licence
*
* @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
* @license  http://ecos.shopex.cn/ ShopEx License
*/
 
return  array(
    'columns' => array(
        'widgets_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('sysapp')->_('挂件实例ID'),
        ),
        'tmpl' => array(
            'type' => 'string',
            'length' => 30,
            'required' => true,
            'label' => app::get('sysapp')->_('页面'),
            'comment' => app::get('sysapp')->_('页面'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'widget' => array(
            'type' => 'string',
            'length' => 30,
            'required' => true,
            'default' => '',
            'label' => app::get('sysapp')->_('挂件名称'),
            'comment' => app::get('sysapp')->_('挂件名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'order_sort' => array(
            'type' => 'smallint',
            'unsigned' => true,
            'default' => 0,
            'required' => true,
            'editable' => false,
            'label' => app::get('sysapp')->_('挂件排序'),
            'comment' => app::get('sysapp')->_('挂件排序'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'params' => array(
            'type' => 'serialize',
            'default' => '',
            'comment' => app::get('sysapp')->_('配置参数'),
        ),
    ),
    'primary' => 'widgets_id',
    'index' => array(
        'ind_tmpl' => ['columns' => ['tmpl']],
        'ind_widget' => ['columns' => ['widget']],
    ),
    'unbackup' => true,
    'comment' => app::get('sysapp')->_('挂件实例表'),
);

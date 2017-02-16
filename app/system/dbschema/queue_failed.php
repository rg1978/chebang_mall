<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'id' => array (
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('system')->_('ID'),
        ),
        'queue_name' => array (
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('system')->_('队列标识'),
            'label' => app::get('system')->_('队列标识'),
            'required' => true,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'data'=>array(
            'type' => 'text',
            'required' => true,
            'comment' => app::get('system')->_('队列数据'),
            'label' => app::get('system')->_('队列数据'),
            'in_list'=>true,
            'default_in_list'=>true,
            'filtertype' => true,
            'filterdefault' => true,
            'searchtype' => 'has',
        ),
        'create_time' => array (
            'type' => 'time',
            'default' => 0,
            'comment' => app::get('system')->_('队列执行失败时间'),
            'label' => app::get('system')->_('创建时间'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'reason' => array(
            'type' => 'text',
            'comment' => app::get('system')->_('失败原因'),
            'label' => app::get('system')->_('失败原因'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
    ),
    'primary' => 'id',
    'index' => array(
        'ind_get' => ['columns' => ['queue_name']],
    ),
    'comment' => app::get('system')->_('队列执行失败表'),
);


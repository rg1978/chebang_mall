<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'tag_id' => array (
            'type'=>'number',
            //'pkey'=>true,
            'autoincrement' => true,
            'label' => app::get('sysuser')->_('标签id'),
            'comment' => app::get('sysuser')->_('标签id'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'tag_name' => array(
            'type' => 'string',
            'length' => 150,
            'label' => app::get('sysuser')->_('标签名称'),
            'comment' => app::get('sysuser')->_('标签名称'),
            'in_list' => true,
            'required' => true,
            'default_in_list' => true,
        ),
        'tag_color' => array(
            'type' => 'string',
            'length' => 7,
            'label' => app::get('sysuser')->_('标签颜色'),
            'comment' => app::get('sysuser')->_('标签颜色'),
        ),
    ),
    'primary' => 'tag_id',
    'index' => array(
        'ind_unique' => [
            'columns' => ['tag_name'],
            'prefix' => 'unique',
        ],
    ),
    'comment' => app::get('sysuser')->_('标签'),
);

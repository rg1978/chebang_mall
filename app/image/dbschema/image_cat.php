<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return [
    'columns' => [
        'image_cat_id' => [
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('image')->_('ID'),
        ],
        'shop_id'=> [
            'type'=>'number',
            'required' => true,
            'comment' => app::get('image')->_('店铺编号id'),
        ],
        'img_type' => [
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('image')->_('图片类型'),
        ],
        'image_cat_name' => [
            'type' => 'string',
            'length' => '100',
            'comment' => app::get('image')->_('图片分类名称'),
        ],
        'last_modified' => [
            'comment'=>app::get('image')->_('更新时间'),
            'type' => 'last_modify',
            'required' => true,
        ]
    ],
    'primary' => 'image_cat_id',
    'comment' => app::get('image')->_('图片类型子分类表')
];


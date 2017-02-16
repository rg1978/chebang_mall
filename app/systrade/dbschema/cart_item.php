<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
// 购物车cart_objects表关联的各个sku基础信息，方便库存判断
return  array(
    'columns' => array(
        'cart_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('systrade')->_('购物车ID'),
            'label' => app::get('systrade')->_('购物车ID'),
        ),
        'sku_id' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('systrade')->_('SKU的ID'),
            'comment' => app::get('systrade')->_('SKU的ID'),
        ),
        'quantity' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('systrade')->_('SKU的数量'),
            'comment' => app::get('systrade')->_('SKU的数量'),
        ),
    ),
    'primary' => ['cart_id', 'sku_id'],
    'unbackup' => true,
    'comment' => app::get('systrade')->_('购物车sku数量信息表'),
);

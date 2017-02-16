<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' =>
    array (
        'police_id' =>
        array (
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('sysshop')->_('库存报警ID'),
        ),
        'shop_id'=>array(
            'type'=>'table:shop',
            'required' => true,
            'comment' => app::get('sysshop')->_('店铺ID'),
            'label' => app::get('sysshop')->_('店铺名称'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'policevalue' =>
        array (
            'type' => 'number',
            'length' => 100,
            'required' => true,
            'label' => app::get('sysshop')->_('库存报警值'),
            'width' => 310,
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => true,
        ),
    ),

    'primary' => 'police_id',
    'index' => array(
        'ind_shop_id' => ['columns' => ['shop_id']],
    ),
    'comment' => app::get('sysshop')->_('库存报警表'),
);

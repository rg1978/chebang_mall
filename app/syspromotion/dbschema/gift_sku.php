<?php
return array(
    'columns' => array(
        'sku_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('货品ID'),
        ),
        'gift_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('赠品方案id'),
        ),
        'item_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('商品ID'),
        ),
        'shop_id' => array(
            'type' => 'number',
            'required' => true,
            'label' => app::get('syspromotion')->_('所属商家'),
            'comment' => app::get('syspromotion')->_('所属商家的店铺id'),
        ),
        'gift_num' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('syspromotion')->_('赠品数量'),
        ),
        'start_time' => array(
            'type' => 'time',
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('起始时间'),
            'comment' => app::get('syspromotion')->_('起始时间'),
        ),
        'end_time' => array(
            'type' => 'time',
            'default'=> 0,
            'editable' => true,
            'in_list' => true,
            'default_in_list' => false,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('截止时间'),
            'comment' => app::get('syspromotion')->_('截止时间'),
        ),
        'status' => array(
            'type' => 'bool',
            'default' => '1',
            'required' => true,
            'label' => app::get('syspromotion')->_('是否生效中'),
            'comment' => app::get('syspromotion')->_('是否生效中'),
        ),
    ),
    'primary' => ['gift_id', 'sku_id'],
    'index' => array(
        'ind_item_id' => ['columns' => ['item_id']],
    ),
    'comment' => app::get('syspromotion')->_('参与赠品促销的货品关联表'),
);


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'append_rate_id' => array(
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('sysrate')->_('追评ID'),
        ),
        'rate_id' => array(
            'type' => 'bigint',
            'required' => true,
            'comment' => app::get('sysrate')->_('评价ID'),
        ),
        'shop_id' =>
        array(
            'type' => 'number',
            'comment' => app::get('sysrate')->_('店铺ID'),
        ),
        'append_content' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('sysrate')->_('追评内容'),
        ),
        'append_rate_pic' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('sysrate')->_('追评图片'),
        ),
        'is_reply' => array(
            'type' => 'bool',
            'default' => '0',
            'comment' => app::get('sysrate')->_('追评是否回复'),
        ),
        'append_reply_content' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('sysrate')->_('追评回复'),
        ),
        'reply_time' => array(
            'type' => 'time',
            'comment' => app::get('sysrate')->_('追评回复时间'),
        ),
        'trade_end_time' => array(
            'type' => 'time',
            'comment' => app::get('sysrate')->_('订单结束时间'),
        ),
        'created_time' => array(
            'type' => 'time',
            'comment' => app::get('sysrate')->_('创建时间'),
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'comment' => app::get('sysrate')->_('最后修改时间'),
        ),
        'disabled' => array(
            'type' => 'bool',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'comment' => app::get('sysrate')->_('是否有效'),
        ),
    ),
    'primary' => 'append_rate_id',
    'index' => array(
        'rate_id' => ['columns' => ['rate_id']],
    ),
    'comment' => app::get('sysrate')->_('商品追加评论'),
);


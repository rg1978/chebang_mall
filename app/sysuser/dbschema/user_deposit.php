<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array(
        'user_id' => array (
            'type' => 'table:account@sysuser',
            'comment' => app::get('systrade')->_('会员用户id'),
        ),
        'deposit' => array(
            'type' => 'money',
            'default' => '0',
            'comment' => app::get('systrade')->_('预存款余额'),
        ),
        'password' => array(
            'type' => 'string',
            'length' => 60,
            'default' => '',
            'comment' => app::get('sysuser')->_('支付密码'),
        ),
    ),
    'primary' => 'user_id',
    'comment' => app::get('sysuser')->_('预存款表'),
);


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'paybill_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement'=> true,
            'comment' => app::get('ectools')->_('子支付单编号'),
            'deny_export' => false,
        ),
        'payment_id' => array(
            'type' => 'string',
            'required' => true,
            'length' => 20,
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('ectools')->_('主支付单编号'),
            'label' => app::get('ectools')->_('支付单号'),
            'searchtype' => 'has',
        ),
        'tid' => array(
            'type' => 'string',
            'required' => true,
            'length' => 20,
            'comment' => app::get('ectools')->_('被支付订单编号'),
            'label' => app::get('ectools')->_('订单号'),
             'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
        ),
        'status' => array(
            'type' => array (
                'succ' => app::get('ectools')->_('支付成功'),
                'failed' => app::get('ectools')->_('支付失败'),
                'cancel' => app::get('ectools')->_('未支付'),
                'error' => app::get('ectools')->_('处理异常'),
                'invalid' => app::get('ectools')->_('非法参数'),
                'progress' => app::get('ectools')->_('已付款至担保方'),
                'timeout' => app::get('ectools')->_('超时'),
                'ready' => app::get('ectools')->_('准备中'),
                'paying' => app::get('ectools')->_('支付中'),
            ),
            'required' => true,
            'default' => 'ready',
            'length' => 20,
            'comment' => app::get('ectools')->_('该订单支付的状态'),
            'label' => app::get('ectools')->_('支付状态'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'payment' => array(
            'type' => 'string',
            'required' => true,
            'length' => 20,
            'comment' => app::get('ectools')->_('该订单支付的金额'),
            'label' => app::get('ectools')->_('支付金额'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'user_id' => array (
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('ectools')->_('会员id'),
        ),
        'created_time' => array (
            'type' => 'time',
            'label' => app::get('ectools')->_('支付开始时间'),
            'comment' => app::get('ectools')->_('支付单创建时间'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'payed_time' => array (
            'type' => 'time',
            'label' => app::get('ectools')->_('支付完成时间'),
            'comment' => app::get('ectools')->_('支付完成时间'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'modified_time' => array (
            'type' => 'time',
            'label' => app::get('ectools')->_('最后修改时间'),
            'comment' => app::get('ectools')->_('最后更新时间'),
            'in_list' => true,
            'default_in_list' => false,
        ),
   ),

   'primary' => 'paybill_id',
    'comment' => app::get('ectools')->_('订单支付单据记录'),
);



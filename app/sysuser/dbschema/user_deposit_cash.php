<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'cash_id' => array(
            'type'=>'bigint',
            'comment' => app::get('sysuser')->_('提现单号'),
            'label' => app::get('sysuser')->_('提现单号'),
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'user_id' => array(
            'type' => 'table:account@sysuser',
            'label' => app::get('sysuser')->_('用户名'),
            'comment' => app::get('sysuser')->_('会员用户id'),
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'create_time' => array(
            'label' => app::get('sysuser')->_('提现申请时间'),
            'comment' => app::get('sysuser')->_('提现申请时间'),
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'amount' => array(
            'type' => 'money',
            'comment' => app::get('sysuser')->_('金额'),
            'label' => app::get('sysuser')->_('金额'),
            'in_list' => false,
            'default_in_list' => false,
        ),
        'bank_card_id' => array(
            'type' => 'string',
            'length' => 20,
            'label' => app::get('sysuser')->_('银行卡号'),
            'comment' => app::get('sysuser')->_('银行卡号'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'bank_name' => array(
            'type' => 'string',
            'length' => 50,
            'label' => app::get('sysuser')->_('开户行名称'),
            'comment' => app::get('sysuser')->_('开户行名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
      //'bank_code' => array(
      //    'type' => 'string',
      //    'length' => 10,
      //    'label' => app::get('sysuser')->_('银行编码'),
      //),
        'bank_card_owner' => array(
            'type' => 'string',
            'length' => 20,
            'label' => app::get('sysuser')->_('持卡人姓名'),
            'comment' => app::get('sysuser')->_('持卡人姓名'),
            'in_list' => true,
            'default_in_list' => true,
        ),
      //'bank_addr' => array(
      //    'type' => 'string',
      //    'length' => 100,
      //    'label' => app::get('sysuser')->_('开户行地址'),
      //),
        'status' => array(
            'type' => array(
                'TO_VERIFY' => app::get('sysuser')->_('已申请'),
                'VERIFIED' => app::get('sysuser')->_('已审核'),
                'DENIED' => app::get('sysuser')->_('已驳回'),
                'COMPELETE' => app::get('sysuser')->_('已完成'),
            ),
            'label' => app::get('sysuser')->_('提现状态'),
            'comment' => app::get('sysuser')->_('提现状态'),
            'in_list' => true,
            'default_in_list' => true,
        ),
      //'denied_reason' => array(
      //    'type' => 'string',
      //    'length' => 200,
      //    'label' => app::get('sysuser')->_('驳回理由'),
      //),
        'serial_id' => array(
            'type' => 'string',
            'length' => 50,
            'label' => app::get('sysuser')->_('交易流水号'),
            'comment' => app::get('sysuser')->_('交易流水号'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'executor' => array(
            'type' => 'string',
            'length' => 20,
            'label' => app::get('sysuser')->_('转账执行人'),
            'comment' => app::get('sysuser')->_('转账执行人'),
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'primary' => 'cash_id',
    'comment' => app::get('sysuser')->_('预存款提现信息表'),
);


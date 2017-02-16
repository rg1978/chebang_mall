<?php
return  array(
    'columns'=>array(
        'id' => array(
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('systrade')->_('ID'),
        ),
        'tid' => array(
            'type' => 'table:trade',
            'required' => true,
            'comment' => app::get('systrade')->_('订单编号'),
        ),
        'shop_id' => array(
            'type' => 'table:shop@sysshop',
            'required' => true,
            'comment' => app::get('systrade')->_('所属商家'),
        ),
        'num' => array(
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'comment' => app::get('systrade')->_('发送次数'),
        ),
        'vcode' => array(
            'type' => 'string',
            'length' => 60,
            'required' => true,
            'comment' => app::get('systrade')->_('提货码'),
        ),
        'status' => array(
            'type' => array(
                'WITH_CHECK' => '等待验证',
                'WITH_FINDISH' => '验证完成',
            ),
            'default' => 'WITH_CHECK',
            'required' => true,
            'comment' => app::get('systrade')->_('提货码验证状态'),
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'comment' => app::get('systrade')->_('修改时间'),
        ),
    ),
    'primary' => 'id',
    'index' => array(
        'ind_tid' => ['columns' => ['tid']],
        'ind_shop_id' => ['columns' => ['shop_id']],
    ),
    'comment' => app::get('systrade')->_('自提订单提货码记录表'),
);


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'refunds_id' => array(
            'type' => 'number',
            'autoincrement' => true,
            //'pkey' => true,
            'required' => true,
            'comment' => app::get('sysaftersales')->_('退款申请ID'),
        ),
        'refund_bn' => array(
            'type' => 'string',
            'comment' => '退款申请编号',
        ),
        'user_id' => array(
            'type' => 'number',
            'comment' => '会员id',
        ),
        'shop_id' => array(
            'type' => 'number',
            'comment' => '店铺id',
        ),
        'tid' => array(
            'type' => 'string',
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'custom',
            'filterdefault' => true,
            'label' => app::get('sysaftersales')->_('订单ID'),
            'comment' => '该退款单的主订单号',
        ),
        'oid' => array(
            'type' => 'string',
            'comment' => '该退款单的订单号',
        ),
        'aftersales_bn' => array(
            'type' => 'string',
            'comment' => app::get('sysaftersales')->_('申请售后编号'),
            'searchtype' => 'has',
            'filtertype' => 'custom',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('售后编号'),
        ),
        'refunds_type' => array(
            'type' => array(
                '0' => '售后申请退款',
                '1' => '取消订单退款',
                '2' => '拒收订单退款',
            ),
            'default' => '0',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('退款类型'),
        ),
        'status' => array(
            'type' => array(
                '0' => '未审核',
                '1' => '已完成退款',
                '2' => '已驳回',
                '3' => '商家审核通过',
                '4' => '商家审核不通过',
                '5' => '商家强制关单',
                '6' => '平台强制关单',
            ),
            'default' => '0',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('审核状态'),
        ),
        'refunds_reason' => array(
            'type' => 'string',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('申请退款原因'),
        ),
        'order_price' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'label' => '订单金额',
        ),
        'total_price' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'label' => '应退金额',
        ),
        'refund_fee' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'label' => '实退金额',
        ),
        'points_fee' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'label' => '积分抵扣金额',
        ),
        'hongbao_fee' => array(
            'type' => 'money',
            'default' => '0',
            'editable' => false,
            'comment' => app::get('sysaftersales')->_('红包支付金额'),
        ),
        'user_hongbao_id' => array(
            'type' => 'string',
            'comment' => app::get('sysaftersales')->_('使用红包支付的ID集合'),
        ),
        'return_freight' => array(
            'type' => array(
                '2' => app::get('sysaftersales')->_('退运费'),
                '3' => app::get('sysaftersales')->_('不退运费'),
            ),
            'default' => '3',
            'comment' => '是否退运费',
            'label' => '是否退运费',
        ),
        'consume_point_fee' => array(
            'type' => 'number',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysaftersales')->_('抵扣的积分'),
            'label' => app::get('sysaftersales')->_('抵扣的积分'),
        ),
        'refund_point' => array(
            'type' => 'number',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysaftersales')->_('实退积分'),
            'label' => app::get('sysaftersales')->_('实退积分'),
        ),

        'created_time' => array(
            'type' => 'time',
            'label' => app::get('sysaftersales')->_('创建时间'),
            'width' => '100',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'label' => app::get('sysaftersales')->_('修改时间'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
        )
    ),
    'primary' => 'refunds_id',
    'index' => array(
        'ind_user_id' => ['columns' => ['user_id']],
        'ind_shop_id' => ['columns' => ['shop_id']],
        'ind_refunds_type' => ['columns' => ['refunds_type']],
        'ind_status' => ['columns' => ['status']],
        'ind_refund_bn' => [
            'columns' => ['refund_bn'],
            'prefix' => 'unique'
        ],
    ),
    'comment' => app::get('sysaftersales')->_('退款申请表'),
);


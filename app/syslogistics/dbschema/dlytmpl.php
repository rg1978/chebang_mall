<?php

return  array(
    'columns' => array(
        'template_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'label' => app::get('syslogistics')->_('模块ID'),
            'width' => 110,
        ),
        'shop_id' => array(
            'type'=>'table:shop@sysshop',
            'required' => true,
            'in_list' => true,
            'default_in_list'=>true,
            'comment' => app::get('syslogistics')->_('店铺名称'),
            'label' => app::get('syslogistics')->_('店铺名称'),
        ),
        'name' => array(
            'type' => 'string',
            'length' => 50,
            'width' => 180,
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => true,
            'comment' => app::get('syslogistics')->_('运费模板名称'),
            'label' => app::get('syslogistics')->_('运费模板名称'),
        ),
        'is_free' => array(
            'type' => array(
                '0' => app::get('syslogistics')->_('自定义运费'),
                '1' => app::get('syslogistics')->_('卖家承担运费'),
            ),
            'default' => '0',
            'comment' => app::get('syslogistics')->_('是否包邮'),
            'label' => app::get('syslogistics')->_('是否包邮'),
        ),
        'valuation' => array(
            'type' => array(
                '1' => app::get('syslogistics')->_('按重量'),
                '2' => app::get('syslogistics')->_('按件数'),
                '3' => app::get('syslogistics')->_('按金额'),
                '4' => app::get('syslogistics')->_('按体积'),
            ),
            'default' => '1',
            'comment' => app::get('syslogistics')->_('运费计算参数来源'),
            'label' => app::get('syslogistics')->_('运费计算参数来源'),
        ),
        'protect' => array(
            'type' => 'bool',
            'default' => 0,
            'required' => true,
            'width' => 75,
            'comment' => app::get('syslogistics')->_('物流保价'),
        ),
        'protect_rate' => array(
            'type' => 'decimal',
            'precision' => 6,
            'scale' => 3,
            'comment' => app::get('syslogistics')->_('保价费率'),
        ),
        'minprice' => array(
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
            'default' => '0.00',
            'required' => true,
            'editable' => false,
            'comment' => app::get('syslogistics')->_('保价费最低值'),
        ),
        'status' => array(
            'type' => array(
                'off' => app::get('syslogistics')->_('关闭'),
                'on' => app::get('syslogistics')->_('启用'),
            ),
            'default' => 'on',
            'comment' => app::get('syslogistics')->_('是否开启'),
            'label' => app::get('syslogistics')->_('是否开启'),
        ),
        'fee_conf' => array(
            'type' => 'text',
            'required' => false,
            'default' => '',
            'comment' => app::get('syslogistics')->_('运费模板中运费信息对象，包含默认运费和指定地区运费'),
            'label' => app::get('syslogistics')->_('运费模板中运费信息对象，包含默认运费和指定地区运费'),
        ),
        'free_conf' => array(
            'type' => 'text',
            'required' => false,
            'default' => '',
            'comment' => app::get('syslogistics')->_('指定包邮的条件'),
            'label' => app::get('syslogistics')->_('指定包邮的条件'),
        ),
        'create_time'=> array(
            'type'=>'time',
            'comment' => app::get('syslogistics')->_('创建时间'),
            'label' => app::get('syslogistics')->_('创建时间'),
        ),
        'modifie_time'=> array(
            'type'=>'last_modify',
            'comment' => app::get('syslogistics')->_('最后修改时间'),
            'label' => app::get('syslogistics')->_('最后修改时间'),
        ),
    ),
    'primary' => 'template_id',
    'index' => array(
        'ind_shop_temp_id' => ['columns' => ['shop_id', 'template_id']],
        'ind_shop_id' => ['columns' => ['shop_id']],
    ),
    'comment' => app::get('syslogistics')->_('快递模板配置表'),
);


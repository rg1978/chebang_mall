<?php
/**
 * ShopEx licence
 * ajx
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 系统配件（邮件短信等配置）
 */

return array(

    'messenger' =>array(
        /*
        |--------------------------------------------------------------------------
        | 电子邮件配置
        |--------------------------------------------------------------------------
         */
        'email' => array(
            'label' => '电子邮件',
            'display' => true,
            'version' => '$ver$',
            'isHtml' => true,
            'hasTitle' => true,
            'allowMultiTarget' => false,
            'targetSplit' => ',',
            'dataname' => 'email',
            'debug' => false,
            'class' => 'system_messenger_email',
        ),

        /*
        |--------------------------------------------------------------------------
        | 手机短信配置
        |--------------------------------------------------------------------------
         */
        'sms' => array(
            'label' => '手机短信',
            'display' => true,
            'version' => '$ver$',
            'isHtml' => false,
            'hasTitle' => false,
            'allowMultiTarget' => false,
            'withoutQueue' => false,
            'dataname' => 'mobile',
            'sms_service_ip' => '124.74.193.222',
            'sms_service' => 'http://idx.sms.shopex.cn/service.php',
            'class' => 'system_messenger_sms',
        ),
    ),
    'actions' => array(

        /*
        |--------------------------------------------------------------------------
        | 身份验证
        |--------------------------------------------------------------------------
         */
        'account-member' => array(
            'label' => '身份验证',
            'email' => 'true',
            'sms' => 'true',
            'sendType' => 'notice',
            'use_reply'=>'false',
            'varmap' => '验证码<{$vcode}>',
        ),

        /*
        |--------------------------------------------------------------------------
        | 手机注册短信验证
        |--------------------------------------------------------------------------
         */
        'account-signup' => array(
            'label' => '手机注册短信验证',
            'email' => 'false',
            'sms' => 'true',
            'sendType' => 'notice',
            'use_reply'=>'false',
            'varmap' => '验证码<{$vcode}>',
        ),

        /*
        |--------------------------------------------------------------------------
        | 手机注册短信找回密码验证
        |--------------------------------------------------------------------------
         */
        'account-lostPw' => array(
            'label' => '找回密码',
            'email' => 'true',
            'sms' => 'true',
            'sendType' => 'notice',
            'use_reply'=>'false',
            'varmap' => '验证码<{$vcode}>',
        ),

        /*
         |--------------------------------------------------------------------------
         | 预存款密码忘记
         |--------------------------------------------------------------------------
         */
        'deposit-lostPw' => array(
                'label' => '预存款密码找回',
                'email' => 'true',
                'sms' => 'true',
                'sendType' => 'notice',
                'use_reply'=>'false',
                'varmap' => '验证码<{$vcode}>',
        ),

        /*
        |--------------------------------------------------------------------------
        | 解绑
        |--------------------------------------------------------------------------
         */
        'account-unmember' => array(
            'label' => '解绑手机邮箱',
            'email' => 'true',
            'sms' => 'true',
            'sendType' => 'notice',
            'use_reply'=>'false',
            'varmap' => '验证码<{$vcode}>',
        ),

        /*
        |--------------------------------------------------------------------------
        | 邮件通知
        |--------------------------------------------------------------------------
         */
        'user-item' => array(
            'label' => '到货通知邮箱',
            'email' => 'true',
            'sms' => 'false',
            'sendType' => 'notice',
            'use_reply'=>'false',
            'varmap' => '邮件<{$vcode}>',
        ),

        /*
        |--------------------------------------------------------------------------
        | 活动开售提醒
        |--------------------------------------------------------------------------
        */
        'activity-remind' => array(
            'label' => '活动开售提醒',
            'email' => 'true',
            'sms' => 'true',
            'sendType' => 'notice',
            'use_reply'=>'false',
            'varmap' => '商品名称<{$item_name}>;活动名称<{$activity_name}>;活动开始时间<{$time}>;商城名称<{$site_name}>;商城路径<{$url}>;',
        ),
        /*
         |--------------------------------------------------------------------------
         | 店铺手机邮箱认证
         |--------------------------------------------------------------------------
         */
        'account-shop' => array(
                'label' => '店铺安全中心认证',
                'email' => 'true',
                'sms' => 'true',
                'sendType' => 'notice',
                'use_reply'=>'false',
                'varmap' => '验证码<{$vcode}>',
        ),

        /*
         |--------------------------------------------------------------------------
         | 店铺密码找回
         |--------------------------------------------------------------------------
         */
        'findPw-shop' => array(
                'label' => '店铺密码找回',
                'email' => 'true',
                'sms' => 'true',
                'sendType' => 'notice',
                'use_reply'=>'false',
                'varmap' => '验证码<{$vcode}>',
        ),

        /*
         |--------------------------------------------------------------------------
         | 发送自提提货验证码
         |--------------------------------------------------------------------------
         */
        'delivery-ziti' => array(
                'label' => '自提提货验证码',
                'email' => 'false',
                'sms' => 'true',
                'sendType' => 'notice',
                'use_reply'=>'false',
                'varmap' => '订单号<{$tid}> 自提地址<{$ziti_addr}> 验证码<{$vcode}>',
        ),
    ),
);


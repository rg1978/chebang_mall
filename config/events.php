<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 返回注册的事件
 */
return [

    /*
    |--------------------------------------------------------------------------
    | 定义事件触发后需要执行的任务
    |--------------------------------------------------------------------------
    |
    */
    'listen' => [
        //触发事件后执行被监听的任务
        //第一个参数为执行任务的执行类和方法 执行方法未指定则默认为handle
        //第二个参数设置该任务为同步执行还是异步执行
        //第三个参数为执行任务的优先级，数值越大则越先执行，相同等级则按照顺序执行 默认为0
        //指定参数 queue 对应为该任务异步执行的队列 默认为system_tasks_events
        'test' => [
            ['system_events_listeners_testSync', 'sync'],
            ['system_events_listeners_testAsync@test', 'async'],
        ],

        //队列失败会触发该事件，如果失败队列后需要发送短信或者邮件则可实现该事件
        #'queueFailed' => [
        #],

        //前台商城用户注册成功触发的事件
        'user.create' => [
            //注册成功后赠送积分
            ['sysuser_events_listeners_sendPoint@sendPoint', 'sync'],
        ],

        //前台商城用户登录成功触发的事件
        'user.login' => [
            //登录成功后设置收藏商品，收藏店铺数据到cookie
            ['pam_events_listeners_collect@login', 'sync'],
            ['pam_events_listeners_itemBrowserHistory', 'sync'],
        ],

        //前台商城用户登出成功触发的事件
        'user.logout' => [
            //退出登录清除收藏商品，收藏店铺数据
            ['pam_events_listeners_collect@logout', 'sync'],
            //登出成功后清除cookie中购物车的数量
            ['pam_events_listeners_cookieWithCartNumber@logout', 'sync'],
        ],

        //用户签到触发的事件
        'user.checkin' => [
            //签到成功后，更新会员积分
            ['sysuser_events_listeners_checkin_point@updateUserInfo', 'sync'],
        ],

        //创建订单触发的事件任务
        'trade.create' => [
            //创建订单成功后清除购物车
            ['systrade_events_listeners_clearCart', 'sync'],
            // 保存最新的下单发票信息
            ['sysuser_events_listeners_saveInvoice', 'sync'],

            //异步
            //创建订单后生成订单日志
            ['systrade_events_listeners_createTradelog@addTradeLog', 'async'],
            ['systrade_events_listeners_createTradelog@addPromotionLog', 'sync'],
            //更新活动商品销量
            ['systrade_events_listeners_upActivitySalesCount', 'sync'],
            //消息通知到prism
            ['systrade_events_listeners_notifyPrism@tradeCreate', 'sync','queue'=>'system_tasks_notifyPrism'],
        ],

        'trade.editPrice' => [
            ['systrade_events_listeners_notifyPrism@tradeEditPrice', 'sync','queue'=>'system_tasks_notifyPrism'],
        ],

        //订单支付完成触发的事件任务
        'trade.pay' => [
            //订单付款成功记录日志
            ['systrade_events_listeners_payTradeLog', 'async'],
            //消息通知到prism
            ['systrade_events_listeners_notifyPrism@tradePay', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //发货完成
        'trade.delivery' => [
            //消息通知到prism
            ['systrade_events_listeners_notifyPrism@tradeDelivery', 'async', 'queue'=>'system_tasks_notifyPrism'],
            ['syslogistics_events_listeners_kdnsubscribe', 'async'],
        ],

        //订单确认收货
        'trade.confirm' => [
            //日志
            ['systrade_events_listeners_confirmTradeLog', 'async'],
            //修改商品销量
            ['systrade_events_listeners_updateSoldQuantity', 'sync'],
            //积分确认
            ['systrade_events_listeners_confirmPoint', 'sync'],
            //经验值
            ['systrade_events_listeners_confirmExperience', 'sync'],
            //消息通知到prism
            ['systrade_events_listeners_notifyPrism@tradeConfirm', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //取消订单
        'trade.close' => [
            ['systrade_events_listeners_notifyPrism@tradeClose', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //订单退款
        'trade.refund' => [
            ['systrade_events_listeners_notifyPrism@tradeRefund', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        'refund.created' => [
            ['sysaftersales_events_listeners_notifyPrism@refundCreated', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        'refund.modified' => [
            ['sysaftersales_events_listeners_notifyPrism@refundModified', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //创建售后
        'aftersales.created' => [
            ['sysaftersales_events_listeners_notifyPrism@afterSalesCreated', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //审查售后
        'aftersales.check' => [
            ['sysaftersales_events_listeners_notifyPrism@afterSalesCheck', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //消费者回寄商品
        'aftersales.buyerReturnGoods' => [
            ['sysaftersales_events_listeners_notifyPrism@buyerReturnGoods', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //换货商品 重新发货
        'aftersales.sellerSendGoods' => [
            ['sysaftersales_events_listeners_notifyPrism@sellerSendGoods', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //更新售后单状态
        'aftersales.updateStatus' => [
            ['sysaftersales_events_listeners_notifyPrism@afterSalesUpdateStatus', 'async', 'queue'=>'system_tasks_notifyPrism'],
        ],

        //更新商品
        'update.item' => [
            ['sysitem_events_listeners_itemDelta', 'sync'],
        ],

         //更新商品审核状态
        'item.updateStatus' => [
            ['sysitem_events_listeners_approve@approve', 'sync'],
        ],

        //更新营销审核状态
        'promotion.updateStatus' => [
            ['syspromotion_events_listeners_promotionApprove@approve', 'sync'],
        ],

        //删除商品
        'del.item' => [
            ['sysitem_events_listeners_itemDelta@delDelta', 'sync'],
        ],

        /**
         * 商品分类相关
         */
        //编辑或者创建分类
        'category.save' => [
            ['syscategory_events_listeners_category_cache@clear', 'sync'],
        ],

        //删除分类
        'category.remove' => [
            ['syscategory_events_listeners_category_cache@clear', 'sync'],
        ],

    ],
];


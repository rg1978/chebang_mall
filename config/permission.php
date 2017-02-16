<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家中心路由权限定义，权限对应到路由的别名
 */
return array(

    'common' => [
        'permission' =>[
            'topshop.index',
            'topshop.browserTip',
            'topshop.nopermission',
            'topshop.home',
            'topshop.signin',
            'topshop.simpleSignin',
            'topshop.postsignin',
            'topshop.signup',
            'topshop.postsignup',
            'topshop.logout',
            'topshop.userexists',
            'topshop.update',
            'topshop.image.loadModal',
            'topshop.image.search',
            'topshop.postupdatepwd',
            'topshop.feedback',
            'toputil.export.view',
            'toputil.export.do',
            'topshop.commonUserMenu',
            'topshop.goods.select',
            'topshop.goods.selected.format',
            'topshop.goods.brandList',
            'topshop.goods.getItem',
            'topshop.auth.safe.index',
            'topshop.auth.safe.checkpwd',
            'topshop.auth.safe.docheckpwd',
            'topshop.auth.safe.auth',
            'topshop.auth.send.code',
            'topshop.auth.check.code',
            'topshop.auth.update.code',
            'topshop.auth.updatecheck.code',
            'topshop.auth.isError.code',
            'topshop.sku.select',
            'topshop.sku.selected.format',
            'topshop.sku.getSku',
        ],
        'nologin' => [
            //'topshop.index',
            'topshop.browserTip',
            'topshop.nopermission',
            'topshop.home',
            'topshop.signin',
            'topshop.simpleSignin',
            'topshop.postsignin',
            'topshop.signup',
            'topshop.postsignup',
            'topshop.userexists',
            'topshop.register.checkPhonePage',
        ]
    ],

    //商品
    'item' => array(
        'label' => '商品',
        'group' => array(
            'showItem'      => ['label' => '查看商品',      'permission' => ['topshop.item.list','topshop.item.search'] ],
            'addItem'       => ['label' => '商品发布',      'permission' => ['topshop.item.add','topshop.item.create','topshop.item.brand','toputil.syscat.nature','toputil.syscat.params','toputil.syscat.spec.props'] ],
            'editItem'      => ['label' => '编辑商品',      'permission' => ['topshop.item.edit','topshop.item.create','topshop.item.brand','toputil.syscat.nature','toputil.syscat.params','toputil.syscat.spec.props','toputil.syscat.spec.selectprops'] ],
            'setStatusItem' => ['label' => '上下架商品',    'permission' => ['topshop.item.setStatus'] ],
            'delItem'       => ['label' => '删除商品',      'permission' => ['topshop.item.delete'] ],
            'showItemCat'   => ['label' => '查看店铺分类',  'permission' => ['topshop.item.cat.index'] ],
            'editItemCat'   => ['label' => '修改店铺分类',  'permission' => ['topshop.item.cat.store','topshop.item.cat.delete'] ],
            'image' => [
                'label' => '图片管理',
                'permission' => [
                    'topshop.image.index', 'topshop.image.delete', 'topshop.image.upname', 'topshop.image.move.cat.loadModal',
                    'topshop.image.move.cat','topshop.image.cat.loadImgCatModal','topshop.image.add.cat','topshop.image.del.cat','topshop.image.update.cat',
                ],
            ],
            'storepolice'   => ['label' => '管理库存报警',  'permission' => ['topshop.storepolice.add','topshop.storepolice.save'] ],
        ),
    ),

    //交易
    'trade' => array(
        'label' => '交易',
        'group' => array(
            'showTrade' => [
                'label' => '订单管理',
                'permission' => [
                    'topshop.trade.index',//订单列表
                    'topshop.trade.search',//订单搜索页面
                    'topshop.trade.postsearch',//订单搜索POST
                    'topshop.trade.detail',//订单详情
                    'topshop.trade.detail.logi',//订单详情查看物流
                    'topshop.trade.detail.memo',//订单详情添加订单备注
                    'topshop.trade.delivery',//订单发货
                    'topshop.trade.close',//订单取消页面加载
                    'topshop.trade.rejection',//订单拒收页面加载
                    'topshop.trade.postclose',//订单取消操作
                    'topshop.trade.modifyPrice',//修改订单价格页面加载
                    'topshop.trade.modifyPrice.post',//修改订单
                    'topshop.trade.godelivery',
                    'topshop.trade.dodelivery',
                    'topshop.trade.finish',//ajax请求订单完成页面
                    'topshop.trade.postfinish',//订单收钱并收货
                    'topshop.trade.cancel.index',//订单取消管理
                    'topshop.trade.cancel.detail',//订单取消详情
                    'topshop.trade.cancel.postsearch',//订单取消提交搜索
                    'topshop.trade.cancel.search',//订单取消搜索
                    'topshop.trade.cancel.check',//审核取消订单
                    'topshop.trade.ajaxSendDeliverySms',//请求发送自提提货码页面
                    'topshop.trade.ajaxCheckDeliveryVcode',//请求验证自提提货码页面
                    'topshop.trade.sendDeliverySms',//发送自提提货码页面
                    'topshop.trade.checkDeliveryVcode',//验证自提提货码页面
                ],
            ],
            'dlytmpl' => [
                'label' => '快递模板配置',
                'permission' => [
                    'topshop.dlytmpl.index', 'topshop.dlytmpl.edit', 'topshop.dlytmpl.save', 'topshop.dlytmpl.delete', 'topshop.dlytmpl.isExists',
                ],
            ],
            'dlycorp' => [
                'label' => '管理物流公司',
                'permission' => [
                    'topshop.dlycorp.index','topshop.dlycorp.save','topshop.dlycorp.cancel',
                ],
            ],
        ),
    ),

    'promotion' => array(
        'label' => '营销',
        'group' => array(
            'fullminus' => [
                'label' => '满减促销',
                'permission' => [
                    'topshop.promotion.fullminus','topshop.promotion.fullminuslist','topshop.fullminus.list', 'topshop.fullminus.edit', 'topshop.fullminus.save', 'topshop.fullminus.delete',
                    'topshop.fullminus.show','topshop.fullminus.cancel',
                ],
            ],
            'fulldiscount' => [
                'label' => '满折促销',
                'permission' => [
                    'topshop.promotion.fulldiscount','topshop.promotion.fulldiscountlist','topshop.fulldiscount.list', 'topshop.fulldiscount.edit', 'topshop.fulldiscount.save', 'topshop.fulldiscount.delete',
                    'topshop.fulldiscount.show','topshop.fulldiscount.cancel',
                ],
            ],
            'coupon' => [
                'label' => '优惠券促销',
                'permission' => [
                    'topshop.promotion.coupon','topshop.promotion.couponlist','topshop.coupon.list', 'topshop.coupon.edit', 'topshop.coupon.save', 'topshop.coupon.delete',
                    'topshop.coupon.show','topshop.coupon.cancel',
                ],
            ],
            'freepostage' => [
                'label' => '免邮促销',
                'permission' => [
                    'topshop.promotion.freepostage','topshop.promotion.freepostagelist','topshop.freepostage.list', 'topshop.freepostage.edit', 'topshop.freepostage.save', 'topshop.freepostage.delete',
                    'topshop.fulldiscount.cancel',
                ],
            ],
            'xydiscount' => [
                'label' => 'X件Y折促销',
                'permission' => [
                    'topshop.promotion.xydiscount','topshop.promotion.xydiscountlist','topshop.xydiscount.list', 'topshop.xydiscount.edit', 'topshop.xydiscount.save', 'topshop.xydiscount.delete',
                    'topshop.xydiscount.show','topshop.xydiscount.cancel',
                ],
            ],
            'package' => [
                'label' => '组合促销',
                'permission' => [
                    'topshop.promotion.package','topshop.promotion.packagelist','topshop.package.list', 'topshop.package.edit', 'topshop.package.save', 'topshop.package.delete',
                    'topshop.package.show','topshop.package.cancel',
                ],
            ],
            'activity' => [
                'label' => '活动促销',
                'permission' => [
                    'topshop.activity.registeredlist','topshop.activity.activitylist','topshop.activity.historyregisteredlist','topshop.activity.historyregistereddetial','topshop.activity.edit','topshop.activity.save','topshop.activity.noregistered.detail',
                    'topshop.activity.canregistered.detail','topshop.activity.canregistered_detail','topshop.activity.canregistered_apply','topshop.activity.canregistered_apply_save','topshop.activity.noregistered_detail',
                ],
            ],
            'gift' => [
                'label' => '赠品促销',
                'permission' => [
                    'topshop.gift.list','topshop.gift.edit','topshop.gift.show','topshop.gift.save','topshop.gift.delete','topshop.gift.cancel'
                ],
            ],

        ),
    ),

    //店铺
    'shop' => array(
        'label' => '店铺',
        'group' => array(
            'shopsetting' => [
                'label' => '店铺配置',
                'permission' => [
                    'topshop.shopsetting.index', 'topshop.shopsetting.save',
                ],
            ],

            'shopnotice' => [
                'label' => '商家通知',
                'permission' => [
                    'topshop.shopnotice','topshop.shopnotice.detail',
                ],
            ],

            'decorate' => [
                'label' => '店铺装修',
                'permission' => [
                    'topshop.decorate.index', 'topshop.decorate.dialog', 'topshop.decorate.save',
                ],
            ],
            'wap_decorate' => [
                'label' => 'wap店铺装修',
                'permission' => [
                    'topshop.wap.decorate.index', 'topshop.wap.decorate.dialog', 'topshop.wap.decorate.saveSort',
                    'topshop.wap.decorate.addTags', 'topshop.wap.decorate.save', 'topshop.wap.decorate.ajaxWidgetsDel',
                    'topshop.wap.decorate.openTags', 'topshop.wap.decorate.ajaxCheckShowItems', 'topshop.wap.decorate.checkImageSlider',
                    'topshop.wap.decorate.searchItem','topshop.wap.decorate.getBrandList',
                ],
            ],
            'shopapply' => [
                'label' => '入驻信息查看',
                'permission' => [
                    'topshop.shopapply.info',
                ],
            ],
            'applycat' => [
                'label' => '申请类目权限',
                'permission' => [
                    'topshop.applycat.list', 'topshop.applycat.ajax', 'topshop.applycat.save',
                    'topshop.applycat.remove', 'topshop.applycat.get',
                ],
            ],
            'article' => [
                'label' => '文章管理',
                'permission' => [
                    'topshop.article.list', 'topshop.article.nodes.list', 'topshop.article.nodes.save', 'topshop.article.nodes.edit',
                    'topshop.article.nodes.del', 'topshop.article.edit','topshop.article.save', 'topshop.article.del',
                ],
            ],

        ),
    ),

    //交易
    'aftersales' => array(
        'label' => '客服',
        'group' => array(
            'aftersales' => [
                'label' => '退换货管理',
                'permission' => [
                    'topshop.aftersales.list','topshop.aftersales.detail','topshop.aftersales.search',
                    'topshop.aftersales.verification','topshop.aftersales.sendConfirm',
                ],
            ],
            'rate' => [
                'label' => '评价管理',
                'permission' => [
                    'topshop.rate.list','topshop.rate.search','topshop.rate.detail','topshop.rate.reply',
                ],
            ],
            'gask' => [
                'label' => '评价管理',
                'permission' => [
                    'topshop.gask.list','topshop.gask.reply','topshop.gask.screening','topshop.gask.delete','topshop.gask.display',
                ],
            ],
            'rateAppeal' => [
                'label' => '评价申诉',
                'permission' => [
                    'topshop.rate.appeal.list','topshop.rate.appeal.search','topshop.rate.appeal.detail','topshop.rate.appeal',
                ],
            ],
            'rateCount' => [ 'label' => '评价概况', 'permission' => [ 'topshop.rate.count', ], ],
        ),
    ),

    'shopinfo' => array(
        'label' => '结算',
        'group' => array(

            'settlement' => [
                'label' => '结算',
                'permission' => [
                    'topshop.settlement','topshop.settlement.detail',
                ],
            ],
        )
    ),
    'sysstat' => array(
        'label' => '报表',
        'group' => array(
            'sysstat' => [
                'label' => '商家运营概况', 'permission' => [ 'topshop.sysstat','topshop.sysstat.sysstat' ],
            ],
            'stattrade' => [
                'label' => '交易数据分析', 'permission' => [ 'topshop.stattrade','topshop.sysstat.stattrade' ],
            ],
            'sysbusiness' => [
                'label' => '业务数据分析', 'permission' => [ 'topshop.sysbusiness','topshop.sysstat.sysbusiness' ],
            ],
            'itemtrade' => [
                'label' => '商品销售分析', 'permission' => [ 'topshop.sysstat.itemtrade.index', 'topshop.sysstat.itemtrade'],
            ],
        )
    ),
    'account' => array(
        'label' => '账号',
        'group' => array(
            'seller' => [
                'label' => '账号管理', 'permission' => [ 'topshop.account.list','topshop.account.edit','topshop.account.modifyPwd','topshop.account.save','topshop.account.delete' ],
            ],
            'roles' => [
                'label' => '角色管理', 'permission' => [ 'topshop.roles.list','topshop.roles.save','topshop.roles.edit','topshop.roles.delete' ],
            ],
            'log' => [
                'label' => '日志管理', 'permission' => [ 'topshop.oplog.list' ],
            ],

        )
    ),
);


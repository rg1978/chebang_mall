<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    /*
    |--------------------------------------------------------------------------
    | 定义所有topapi api接口路由
    |--------------------------------------------------------------------------
    | v1 表示API版本号
    | theme.modules API调用方法
    | topapi_api_v1_theme_modules API实现类默认调用handle方法
     */
    'routes' => array(
        'v1' => [
            'region.json' => ['uses'=>'topapi_api_v1_regionJson'],
            //物流模块
            'logistics.list.get' => ['uses'=>'topapi_api_v1_logistics_list', 'auth'=>true],
            'logistics.send' => ['uses'=>'topapi_api_v1_logistics_send', 'auth'=>true],

            // 模板模块
            'theme.modules'         => ['uses'=>'topapi_api_v1_theme_modules'],

            // 类目
            'category.itemCategory' => ['uses'=>'topapi_api_v1_category_itemCategory'],

            //用户登录注册
            'user.login'         => ['uses'=>'topapi_api_v1_user_login'],
            'user.logout'        => ['uses'=>'topapi_api_v1_user_logout', 'auth'=>true],
            'user.signup'        => ['uses'=>'topapi_api_v1_user_signup'],
            'user.license'       => ['uses'=>'topapi_api_v1_user_license'],
            'user.verifyAccount' => ['uses'=>'topapi_api_v1_user_verifyAccount'],
            'user.sendSms'       => ['uses'=>'topapi_api_v1_user_sendSms'],
            'user.verifySms'     => ['uses'=>'topapi_api_v1_user_verifySms'],
            'user.vcode'         => ['uses'=>'topapi_api_v1_user_vcode'],

            //会员中心
            'member.index' => ['uses'=>'topapi_api_v1_member_index', 'auth'=>true],
            'member.basics.update' => ['uses'=>'topapi_api_v1_member_updateBasics', 'auth'=>true],
            'member.basics.get' => ['uses'=>'topapi_api_v1_member_getBasics', 'auth'=>true],
            'member.setAccount' => ['uses'=>'topapi_api_v1_member_setAccount', 'auth'=>true],

            //收货地址
            'member.address.list' => ['uses'=>'topapi_api_v1_member_address_list', 'auth'=>true],
            'member.address.get' => ['uses'=>'topapi_api_v1_member_address_get', 'auth'=>true],
            'member.address.create' => ['uses'=>'topapi_api_v1_member_address_create', 'auth'=>true],
            'member.address.update' => ['uses'=>'topapi_api_v1_member_address_update', 'auth'=>true],
            'member.address.delete' => ['uses'=>'topapi_api_v1_member_address_delete', 'auth'=>true],
            'member.address.setDefault' => ['uses'=>'topapi_api_v1_member_address_setDefault', 'auth'=>true],

            //预存款
            'member.deposit.detail' => ['uses'=>'topapi_api_v1_member_deposit_detail', 'auth'=>true],
            //优惠券列表
            'member.coupon.list' => ['uses'=>'topapi_api_v1_member_coupon_list', 'auth'=>true],
            //积分明细
            'member.point.detail' => ['uses'=>'topapi_api_v1_member_point_detail', 'auth'=>true],

            'member.aftersales.list' => ['uses'=>'topapi_api_v1_member_aftersales_list', 'auth'=>true],
            'member.aftersales.get' => ['uses'=>'topapi_api_v1_member_aftersales_get', 'auth'=>true],

            'member.favorite.item.list' => ['uses'=>'topapi_api_v1_member_favorite_item', 'auth'=>true],
            'member.favorite.item.remove' => ['uses'=>'topapi_api_v1_member_favorite_removeItem', 'auth'=>true],
            'member.favorite.item.add' => ['uses'=>'topapi_api_v1_member_favorite_addItem', 'auth'=>true],
            'member.favorite.shop.list' => ['uses'=>'topapi_api_v1_member_favorite_shop', 'auth'=>true],
            'member.favorite.shop.remove' => ['uses'=>'topapi_api_v1_member_favorite_removeShop', 'auth'=>true],
            'member.favorite.shop.add' => ['uses'=>'topapi_api_v1_member_favorite_addShop', 'auth'=>true],

            'member.rate.list' => ['uses'=>'topapi_api_v1_member_rate_list', 'auth'=>true],
            'member.rate.add' => ['uses'=>'topapi_api_v1_member_rate_add', 'auth'=>true],

            'member.complaints.create' => ['uses'=>'topapi_api_v1_member_complaints_create', 'auth'=>true],
            'member.complaints.close' => ['uses'=>'topapi_api_v1_member_complaints_close', 'auth'=>true],
            'member.complaints.list' => ['uses'=>'topapi_api_v1_member_complaints_list', 'auth'=>true],
            'member.complaints.get' => ['uses'=>'topapi_api_v1_member_complaints_get', 'auth'=>true],
            'member.complaints.get' => ['uses'=>'topapi_api_v1_member_complaints_get', 'auth'=>true],

            //安全中心
            'member.security.checkLoginPassword' => ['uses'=>'topapi_api_v1_member_security_checkLoginPassword', 'auth'=>true],
            'member.security.updateLoginPassword' => ['uses'=>'topapi_api_v1_member_security_upLoginPassword', 'auth'=>true],
            'member.security.setPayPassword' => ['uses'=>'topapi_api_v1_member_security_setPayPassword', 'auth'=>true],
            'member.security.checkPayPassword' => ['uses'=>'topapi_api_v1_member_security_checkPayPassword', 'auth'=>true],
            'member.security.updatePayPassword' => ['uses'=>'topapi_api_v1_member_security_updatePayPassword', 'auth'=>true],

            //订单
            'trade.list' => ['uses'=>'topapi_api_v1_trade_list', 'auth'=>true],
            'trade.get' => ['uses'=>'topapi_api_v1_trade_get', 'auth'=>true],
            'trade.cancel.list' => ['uses'=>'topapi_api_v1_trade_cancel_list', 'auth'=>true],
            'trade.cancel.get' => ['uses'=>'topapi_api_v1_trade_cancel_get', 'auth'=>true],
            'trade.cancel.create' => ['uses'=>'topapi_api_v1_trade_cancel_create', 'auth'=>true],
            'trade.confirm' => ['uses'=>'topapi_api_v1_trade_confirm', 'auth'=>true],

            // 商品
            'item.search' => ['uses'=>'topapi_api_v1_item_itemSearch'],
            'item.detail' => ['uses'=>'topapi_api_v1_item_itemDetail'],
            'item.desc' => ['uses'=>'topapi_api_v1_item_itemDesc'],
            'item.rate.list' => ['uses'=>'topapi_api_v1_item_itemRateList'],

            //购物车
            'cart.get' => ['uses'=>'topapi_api_v1_cart_getCart', 'auth'=>true],
            'cart.get.basic' => ['uses'=>'topapi_api_v1_cart_getBasicCart', 'auth'=>true],
            'cart.add' => ['uses'=>'topapi_api_v1_cart_addCart', 'auth'=>true],
            'cart.del' => ['uses'=>'topapi_api_v1_cart_delCart', 'auth'=>true],
            'cart.update' => ['uses'=>'topapi_api_v1_cart_updateCart', 'auth'=>true],
            'cart.count' => ['uses'=>'topapi_api_v1_cart_countCart', 'auth'=>true],
            'cart.checkout' => ['uses'=>'topapi_api_v1_cart_checkoutCart', 'auth'=>true],
            'cart.total' => ['uses'=>'topapi_api_v1_cart_totalCart', 'auth'=>true],
            'cart.user.point' => ['uses'=>'topapi_api_v1_cart_userPoint', 'auth'=>true],

            // 促销
            'promotion.activity.list' => ['uses'=>'topapi_api_v1_promotion_activityList'],
            'promotion.activity.detail' => ['uses'=>'topapi_api_v1_promotion_activityDetail'],
            'promotion.shop.cartpromotion.detail' => ['uses'=>'topapi_api_v1_promotion_promotionDetail'],
            'promotion.shop.coupon.detail' => ['uses'=>'topapi_api_v1_promotion_couponDetail'],

            // 店铺
            'shop.index' => ['uses'=>'topapi_api_v1_shop_indexmodule'],
        ]
    ),
);

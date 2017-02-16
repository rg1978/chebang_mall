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
    | use sysim
    |--------------------------------------------------------------------------
    |
    | 是否使用sysim提供的im服务。
    | 如果关闭，则开启旧的im控件；如果开启，则关闭旧的im控件，显示新的im控件
    |
    */
    //该配置已经作废，请在平台后台修改
    'enable'=>false,

    /*
    |--------------------------------------------------------------------------
    | the set of page whitch has im button
    |--------------------------------------------------------------------------
    |
    | 有客服按钮的页面。
    | index => 首页
    | help => 帮助中心
    | guest => 客户中心
    | assessment => 我的评价
    | consultation => 我的咨询
    |
    | shop => 店铺首页
    | itemInfo => 商品详情页
    | tradeList => 订单列表页
    | tradeInfo => 订单列表页
    | aftersales => 售后页
    | cart => 购物车
    |
    */
    'positionArea' => ['index', 'help', 'guest', 'assessment', 'consultation', 'shop', 'itemInfo', 'tradeList', 'tradeInfo', 'aftersales', 'cart'],

    /*
    |--------------------------------------------------------------------------
    | im plugin config
    |--------------------------------------------------------------------------
    |
    | 这里用来配置使用哪个im工具
    | 365Webcall  toputil_im_plugin_webcall
    | QQ toputil_im_plugin_qq
    | 旺旺 toputil_im_plugin_wangwang
    |
    */
    //该配置已经作废，请在平台后台修改
    'plugin' => 'toputil_im_plugin_wangwang',

    /*
    |--------------------------------------------------------------------------
    | 365webcall专有配置
    |--------------------------------------------------------------------------
    |
    | 用来配置一些平台的365webcall的参数
    |
    */
    '365webcall' => [
        //代理商accountid
        'accountId' => 'shopex',
        //代理商后台地址
        'agentlogin' => 'http://shopex.365webcall.com/AgentLogin.aspx',
        //账户管理地址
        'userlogin'  => 'http://shopex.365webcall.com/',
    ],


);

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_module_config {

    // 挂件类型
    public $widgets = [
        'category_nav' => '商品分类展示',
        'floor' => '楼层',
        'icons_nav' => '快捷导航',
        'slider' => '轮播',
        'single_pic' => '单图',
        'double_pics' => '一行双图',
    ];

    // 页面类型
    public $tmpls = [
        'index' => '首页',
        'activityindex' => '活动首页',
    ];

    // 对应app端页面类型，用于app端判断怎么跳转页面
    public $linktype = [
        'topics' => '专题页',
        'catlist' => '分类页',
        'item' => '商品详情页',
        'member' => '会员中心页',
        'content' => '文章详情页',
        'shopcenter' => '店铺页',
        'activity' => '活动详情页',
        'promotion' => '促销详情页',
        'h5' => '自定义h5页',
    ];

}//End Class

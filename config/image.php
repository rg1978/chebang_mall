<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    /**
     |--------------------------------------------------------------------------
     | 图片支持的格式
     | 暂时只支持 png, jpg, gif, jpeg ，其他图片格式GD库不支持重新生成其他规格图片
     |--------------------------------------------------------------------------
     */
    'image_support_filetype' => ['png', 'jpg', 'gif', 'jpeg'],

     //图片上传文件最大限制，如果上传大小超出PHP.ini配置，请修改PHP.ini
     'uploadedFileMaxSize' => 1024*1024*2,// 2M

    /**
     | 不同类型的图片生成不同的图片规格，本地上传节省空间
     | 如果没有定义image_type 那么上传的图片默认为 normal
     |--------------------------------------------------------------------------
     | normal 普通图片不会生成其他规格图片，默认只有原图和生成一个微图
     |        因此在上传图片的时候尽量上传合适的图片
     |
     | item   商品图片，商品图片会根据运营平台图片配置，生成对于规格的图片
     |        商品图片会根据运营平台图片配置，生成对于规格的图片
     |
     | info   该图片为用户上传图片，需要将上传的图片生成大小两个规格的图片
     |        (评价上传图片, 售后上传凭证图片, 订单投诉上传图片，店铺评价申诉图片)
     |--------------------------------------------------------------------------
     */
    'image_setting' => array(
        'normal' => array(
            'size'=>['T'],
        ),
        'item' => array(
            'size'=>['L','M','S','T'],
            'image_type' => ['item','sysitem'],
        ),
        'info' => array(
            'size'=>['L','T'],
            'image_type' => [//对应images表中的img_type
                'aftersales','rate','complaints',
            ],
        ),
    ),

    //图片类型
    'image_type' => [
        'admin' => [
            'admin', //运营平台图片
            'sysitem', //系统默认图片
        ],
        'seller' => [
            'shop_apply' //商家入住图片 因为商家入住申请还未开通店铺
        ],
        'shop' => [
            'item', //产品图片
            'shop'  //店铺图片
        ],
        'user' => [
            'complaints', // 用户投诉商家图片
            'aftersales', // 售后图片
            'rate' //评价图片
        ],
    ],

    //是否重新谁知图片配置，不使用默认图片配置
    'image_setting_set' => array('item'),

    /**
     |--------------------------------------------------------------------------
     | 图片配置默认大小规格
     | 运营平台可在 图片管理－》图片配置 中修改默认配置用于商品图片
     |--------------------------------------------------------------------------
     */
    'image_default_set' => array (
        'L' =>
        array (
            'width' => '750',
            'height' => '750',
            'title'=>'大图',
        ),
        'M' =>
        array (
            'width' => '640',
            'height' => '640',
            'title'=>'中图',
        ),
        'S' =>
        array (
            'width' => '440',
            'height' => '440',
            'title'=>'小图'
        ),
        'T' =>
        array (
            'width' => '220',
            'height' => '220',
            'title'=>'微图'
        ),
    ),
);

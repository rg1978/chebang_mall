<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


return [

    /*
    |--------------------------------------------------------------------------
    | 默认缓存存储场景(Cache Resource)
    |--------------------------------------------------------------------------
    |
    | 这个选项用来当`Cache Store`没有明确缓存存储资(Cache Resource)源时, 默认指定的缓存存储资源,
    |
    */
    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | 设置缓存开启或关闭
    |--------------------------------------------------------------------------
    |
    | 这个选项用来当没有明确缓存存储场景时, 默认指定的缓存存储场景,
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | 当设置缓存关闭时, 哪几个`store`忽略此设置
    |--------------------------------------------------------------------------
    |
    | 当设置缓存关闭时, 哪几个`store`忽略此设置,
    | 因为在开发环境下关闭缓存进行调试下, 类似`session`这样的场景, 仍然希望`sesion`奏效,
    | 否则就连登陆也不能够了
    |
    */
    'disabled_except' => ['session', 'vcode', 'sysuser'],

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | 这里定义了应用系统所有的缓存使用场景(stores), 每个store可以设定独立的缓存资
    | 源(resoource).
    | 当定义的场景没有指定资源(resource), 会使用`default`指定的缓存资源.
    |
    */
    'stores' => [

        'session' => [
            'title' => '会话',
            'memo' => '访问Web应用程序的每个用户都生成一个单独的Session. Session会保存用户当次访问的运行时数据, 不能local',
            'resource' => 'remote-session',
        ],

        'vcode' => [
            'title' => '验证码',
            'memo' => '验证码, 不能local',
            'resource' => 'remote-session',
        ],

        'qrcode' => [
            'title' => '二维码',
            'memo' => '用于存储URL地址转换为二维码图片的URI的数据',
            'resource' => 'default',
        ],

        'misc' => [
            'title' => '杂项',
            'memo' => '区域数据/desktop tab数值等',
            'resource' => 'default',
        ],

        'compiler' => [
            'title' => '系统模板缓存',
            'memo' => '系统模板缓存, 建议使用`APC`/`secache`之类的本地缓存的方式, 建议local',
            'resource' => 'local',
        ],

        // 用户相关缓存
        // 1. password_lock
        'sysuser' => [
            'title' => '密码锁定',
            'memo' => '用于多次密码错误后锁定密码',
            'resource' => 'remote-session',
        ],

        'theme-widgets' => [
            'title' => '模板挂件区内容缓存',
            'memo' => '模板挂件区内容缓存, 建议使用`APC`/`secache`之类的本地缓存的方式, 建议nlocal',
            'resource' => 'null',
            'params' => [
                // ttl单位为分钟,
                'ttl' => 1,
            ],
        ],

        'controller-cache' => [
            'title' => '整页缓存',
            'memo' => '整页缓存, 建议用remote缓存',
            'resource' => 'default'
        ],
        
        // 商家店铺相关缓存
        // 1. 店铺装修挂件
        'sysdecorate' => [
                'title' => '店铺挂件',
                'memo' => '用于存储店铺挂件参数，建议使用`APC`/`secache`之类的本地缓存的方式, 建议local',
                'resource' => 'local',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Resources
    |--------------------------------------------------------------------------
    |
    | 这里定义缓存的所有资源,在stores里需要制定对应的资源
    | `resource`
    | `driver`分为两类,
    | local(本地): 数据存储在本地Web服务器上, apc secache.
    | remote(中心化): memcached
    | `driver` 默认配置里分为三类
    |
    |
    */
    'resources' => [
        // 此条勿要删除, 系统默认使用
        'null'=> [
            'driver' => 'null'
        ],

        // driver: `local`类型
        'local' => [
            'driver' => 'secache',
            'file' => 'local',
            'size' => '100m',
        ],

        // driver: `remote`类型
        'default' => [
            'driver' => 'secache',
            'file' => 'common',
            'size' => '100m',
        ],

        'remote-session' => [
            'driver' => 'secache',
            'file' => 'session',
            'size' => '1g',
        ],

        'apc' => [
            'driver' => 'apc'
        ],

        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100],
            ],
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */

    'prefix' => 'luckymall',

];


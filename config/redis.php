<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://www.shopex.cn/ ShopEx License
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Redis Connections
    |--------------------------------------------------------------------------
    |
    | 所有Redis连接资源
    | 默认值为: tcp://127.0.0.1:6379
    |
    */
    'connections' => [
        'default' => [
            'servers' => [
                '%CONNECTION%',
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Scenes
    |--------------------------------------------------------------------------
    |
    | 定义了Redis使用场景, 这里定义了什么场景下使用哪个connection .
    | 必须指定场景才能使用场景.
    |
    */
    'scenes' => [
        'queue' => [
            'connection' => 'default',
        ],

        'system' => [
            'connection' => 'default',
        ],

        // 评价系统相关数据
        'sysrate' => [
            'connection' => 'default',
        ],

        // 物流中心相关数据
        // 1. 区域数据
        'syslogistics' => [
            'connection' => 'default',
        ],
        //消费者商品浏览历史纪录
        'browserHistory' => [
            'connection' => 'default',
        ],
        // 用户相关信息
        'sysuser' => [
            'connection' => 'default',
        ],
        // 营销活动审核记录
        'syspromotion' => [
            'connection' => 'default',
        ],
        // 商品上下架审核记录
        'sysitem' => [
            'connection' => 'default',
        ],
        'sysapp' => [
            'connection' => 'default',
        ],
        'topapi_token' => [
            'connection' => 'default',
        ],
        'hongbao' => [
            'connection' => 'default',
        ],
        'topdev' => [
            'connection' => 'default',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis script
    |--------------------------------------------------------------------------
    | 注册的scripts
    |
    */
    'scripts' => [
        'queueGet' => 'base_redis_scripts_listQueueGetFirst',
        'queueMigrate' => 'base_redis_scripts_migrateQueueJobs',
    ],
];

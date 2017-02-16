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
    | 默认队列处理类
    |--------------------------------------------------------------------------
    |
    | 默认缓存处理类
    | 目前支持 system_queue_adapter_mysql,system_queue_adapter_redis,system_prism_queue_adapter
    | 对应原系统:  QUEUE_SCHEDULE
    |
    */
    'default' => 'system_queue_adapter_mysql',

    /*
    |--------------------------------------------------------------------------
    | 默认发送队列
    |--------------------------------------------------------------------------
    |
    | 默认发送队列, 对应queues的定义. 请检查核对, 避免错误
    | 对应原系统:  DEFAULT_PUBLISH_QUEUE
    |
    */
    'default_publish_queue' => 'normal',

    /*
    |--------------------------------------------------------------------------
    | 任务和队列绑定关系
    |--------------------------------------------------------------------------
    |
    | 任务和队列绑定关系, 确认哪个任务发送到哪个队列
    | 如果是定时任务触发的任务, 任务key加前缀crontab:
    |
    */
    'bindings' => array(
        'crontab:site_tasks_createsitemaps' => array('slow'),
        'crontab:ectools_tasks_statistic_day' => array('slow'),
        'crontab:ectools_tasks_statistic_hour' => array('slow'),
        'crontab:apiactionlog_tasks_cleanexpiredapilog' => array('slow'),
        #'crontab:archive_tasks_partition' => array('slow'),

        'desktop_tasks_runimport' => array('normal'),
        'desktop_tasks_turntosdf' => array('normal'),
        'emailbus_tasks_sendemail' => array('slow'),
        'image_tasks_imagerebuild' => array('normal'),
        'recommended_tasks_update' => array('slow'),
        'importexport_tasks_runexport'=>array('slow'),
        'importexport_tasks_runimport'=>array('slow'),

        'aftersales_tasks_archive_returnProduct' => array('slow'),// 订单归档相关

        'system_tasks_events' => array('quick'),

        'system_tasks_notifyPrism' => array('notifyPrism'),

        'other' => array('other'),
    ),

    /*
    |--------------------------------------------------------------------------
    | 定义队列
    |--------------------------------------------------------------------------
    |
    | title: 简要说明
    | thread: 处理进程数
    |
    */
    'queues' => array(
        'slow' => array(
            'title' => 'slow queue',
            'thread' => 3,
            'app' =>'topc',
        ),
        'quick' => array(
            'title' => 'quick queue',
            'thread' => 5,
            'app' =>'topc',
        ),
        'normal' => array(
            'title' => 'normal queue',
            'thread' => 3,
            'app' =>'topc',
        ),
        'notifyPrism' => array(
            'title' => 'notifyPrism queue',
            'thread' => 1,
            'app' =>'topc',
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | consume配置
    |--------------------------------------------------------------------------
    |
    | 配置一些消费者的参数
    |
    */
    'consume' => array(
        //队列线程在执行多少次任务后自杀
        'maxRunTimes' => 20000,
        'timerTickTime'=> 10000,
    ),



    /*
    |--------------------------------------------------------------------------
    | 动作定义
    |--------------------------------------------------------------------------
    |
    | 定义一个动作会触发多少工作
    |
    */
    'action' => array(
        //'action_name' => array('exchange_name'=>'worker_class'),
    ),
);

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

return [
    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     */

    'enabled' => false,

        /*
     |--------------------------------------------------------------------------
     | DebugBar route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by DebugBar to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix' => '_debugbar',

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => array(
        'phpinfo'         => true,  // Php version
        'messages'        => true,  // Messages
        'time'            => false,  // Time Datalogger
        'memory'          => true,  // Memory usage
        'exceptions'      => true,  // Exception displayer
        'db'              => true,  // Show database (PDO) queries and bindings
        'log'             => true,  // Logs from Monolog (merged in messages if enabled)
        'files'           => true, // Show the included files
        'request'         => true,  // Only one can be enabled..
        'route'           => true,  // Current route information
        'views'           => true,  // Views with their data

        /** todo: 以下配置为后续扩展, 请勿使用

        'events'          => false, // All events fired
        'mail'            => true,  // Catch mail messages
        'config'          => true, // Display config settings
        'auth'            => false, // Display Laravel authentication status
        'gate'            => false, // Display Laravel Gate checks
        'session'         => true,  // Display session data
        */
    ),

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'exceptions' => [
            'chain' => true, // 是否显示错误链
        ],

        'db' => [
            'timeline'          => true,  // Add the queries to the timeline
            'explain' => array(            // EXPERIMENTAL: Show EXPLAIN output on queries
                'enabled' => true,
                //'types' => array('SELECT', 'INSERT', 'UPDATE', 'DELETE', 'REPLACE'), // array('SELECT', 'INSERT', 'UPDATE', 'DELETE'); for MySQL 5.6.3+
                'types' => array('SELECT'), // array('SELECT', 'INSERT', 'UPDATE', 'DELETE'); for MySQL 5.6.3+
            ),

            'hints'             => true,    // Show hints for common mistakes

        ],
        'views' => array(
            'data' => false,    //Note: Can slow down the application, because the data can be quite large..
        ),

        'route' => array(
            'label' => true  // show complete route on bar
        ),

    ],
];

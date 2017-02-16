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
    | System Debug Mode
    |--------------------------------------------------------------------------
    |
    | 当开启调试模式, 详细的错误会暴露出来, 否则会提示错误页
    | 对应原系统: DEBUG_PHP + DEBUG_CSS + DEBUG+JS
    |
    */
    'debug' => true,

    /*
    |--------------------------------------------------------------------------
    | System Url
    |--------------------------------------------------------------------------
    |
    | 定义此URL会在几种情况下使用
    | 1. 执行命令行
    | 2. 执行系统级命令
    | 3. 当路由定义了 domain, 但当有部分路由没有定义domain时. 会使用此url作为domain 
    | 对应原系统: BASE_URL
    |
    */
    'url' => '%URL%',

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    | 对应原系统:DEFAULT_TIMEZONE
    |
    */

    'timezone' => '%TIMEZONE%',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    | 对应原系统: LANG
    |
    */
    'locale' => 'zh_CN',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    | 对应原系统: APP_STATICS_HOST
    |
    */
    //'statics_host' => 'http://img.demo.cn;http://img1.demo.cn',

);



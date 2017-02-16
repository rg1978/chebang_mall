<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

error_reporting(E_ERROR | E_USER_ERROR | E_PARSE | E_COMPILE_ERROR);

if (version_compare(phpversion(), '5.6', '>='))
{
    $rawPost = ini_get('always_populate_raw_post_data');
    if ($rawPost !== false && $rawPost != -1)
    {
        echo 'php5.6版本里面必须把php.ini里面的always_populate_raw_post_data值设置成-1'."</br>";
        echo 'Must be set \'always_populate_raw_post_data\' to \'-1\' in php.ini and use the php://input stream instead.';
        exit(1);
    }
}

if (!extension_loaded('mcrypt'))
{
	echo 'Mcrypt PHP extension required.'.PHP_EOL;

	exit(1);
}

kernel::startExceptionHandling();


//todo:
//if ($env != 'testing') ini_set('display_errors', 'Off');

/*
|--------------------------------------------------------------------------
| Set The Default Timezone
|--------------------------------------------------------------------------
|
| Here we will set the default timezone for PHP. PHP is notoriously mean
| if the timezone is not explicitly set. This will be used by each of
| the PHP date and date-time functions throughout the application.
|*/

$config = config::get('app');
$timezone = $config['timezone']?:8;
date_default_timezone_set('Etc/GMT'.($timezone>=0?($timezone*-1):'+'.($timezone*-1)));


$serviceProviders = [
    'debugbar_serviceProvider',
];

foreach ($serviceProviders as $serviceProvider) {
     with(new $serviceProvider)->boot();
}


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

define('ECOS_START', microtime(true));
/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/
require __DIR__.'/../vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| Register Ecos Auto Loader
|--------------------------------------------------------------------------
|
| 娉ㄥ唽Ecos auto loader
|
*/
require __DIR__.'/../app/base/autoload.php';
\ClassLoader::register();


/*
|--------------------------------------------------------------------------
| 添加alias列表到ClassLoader
|--------------------------------------------------------------------------
|
| 添加alias列表到ClassLoader
|
*/
$aliases = require __DIR__.'/aliases.php';
\ClassLoader::addAliases($aliases);

/*
|--------------------------------------------------------------------------
| Include The Compiled Class File
|--------------------------------------------------------------------------
|
| To dramatically increase your application's performance, you may use a
| compiled class file which contains all of the classes commonly used
| by a request. The Artisan "optimize" is used to create this file.
|
*/

$compiledPath = __DIR__.'/cache/compiled.php';

if (file_exists($compiledPath)) {
	require $compiledPath;
}

//Patchwork\Utf8\Bootup::initMbstring();
#!/usr/bin/env php
<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author bryant.yan@gmail.com
 */


error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);

$root_dir = realpath(dirname(__FILE__).'/../../');
$script_dir = $root_dir.'/script';

define('LOG_LEVEL', LOG_INFO);
define('LOG_TYPE', 3);
//define('LOG_FILE', $root_dir.'/data/logs/queue/{date}.php');

//-------------------------------------------------------------------------------------
require_once($script_dir."/lib/runtime.php");


set_error_handler('error_handler');

//-------------------------------------------------------------------------------------


$qb = app::get('base')->database()->createQueryBuilder();
$kvDatas = $qb->select('*')->from('base_kvstore')->execute()->fetchAll();

foreach ($kvDatas as $data) {
    $v = unserialize($data['value']);
    if (is_array($v)) {
        $v = json_encode($v);
    }

    if (starts_with($data['key'], 'service_last_modified')) {
        $pos = strpos($data['key'], '.');
        $key = substr($data['key'], 0, $pos);
        $hkey = substr($data['key'], $pos + 1);
        redis::scene('system')->hset($key, $hkey, $v);
        echo 'system:'.$key. ' - '. $hkey.PHP_EOL;
    } elseif(starts_with($data['key'], 'theme_last_modified')) {
        $key = 'theme_last_modified';
        $hkey = substr($data['key'], strlen($key));
        redis::scene('system')->hset($key, $hkey, $v);
        echo 'system:'.$key. ' - '. $hkey.PHP_EOL;
    } elseif(starts_with($data['prefix'], 'lang/')) {

    } elseif ($data['prefix'] == 'prism') {
        $key = system_prism_store::getPrismkey();
        $hkey = $data['key'];
        redis::scene('system')->hset($key, $hkey, $v);
        echo 'system:'.$key. ' - '. $hkey.PHP_EOL;
    } elseif(starts_with($data['key'], 'syscache_last_modified')) {
        
    } elseif ($data['prefix'] == 'sysrate_dsr') {
        $key = sysrate_dsr::REDIS_KEY;
        $hkey = explode('_', $data['key'])[1];
        redis::scene('sysrate')->hset($key, $hkey, $v);
        echo 'system:'.$key. ' - '. $hkey.PHP_EOL;
    }  else {
        $key = $data['key'];
        redis::scene('system')->set($key, $v);
        echo 'system:'.$key.PHP_EOL;
        echo $key.PHP_EOL;
    }
}

syscache::instance('tbdefine')->set_last_modify();
syscache::instance('service')->set_last_modify();
syscache::instance('setting')->set_last_modify();
echo 'Syscache updated!'.PHP_EOL;

array_walk(array_keys(cache::getStoreResourcesConfig()), function($resource){
    cache::resource($resource)->flush();
 });
echo 'Cache cleaded!'.PHP_EOL;

$storeResources = array_map(function($store) {
    
 }, cache::getStoreResourcesConfig());

function createKey($prefix, $key) {
    return $key;
}



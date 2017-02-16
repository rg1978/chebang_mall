<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class base_rpc_logger
{
    static $api_call_executions = 0;

    static public function apiLog($appName, $method, $parameters, $identity)
    {
        logger::debug("api call: " . ++static::$api_call_executions . ' ' . json_encode(['app'=>$appName, 'method'=>$method, 'param'=> $parameters, 'identity'=>$identity]));
    }

}


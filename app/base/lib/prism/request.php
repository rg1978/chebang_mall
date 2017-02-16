<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_prism_request
{

    static protected $_item = array();

    //获取prism生成的请求id，可以在prism上面查询详细日志
    static public function getRequestId()
    {
        if(!static::$_item['requestId'])
        {
            static::$_item['requestId'] = input::header('x-request-id');
        }

        return static::$_item['requestId'];
    }

    //获取全部的请求数据
    static public function getParams()
    {
        if(!static::$_item['params'])
        {
            static::$_item['params'] = input::get();

        }

        return static::$_item['params'];
    }

    //获取OAuth数据
    static public function getOauthInfo()
    {
        if(!static::$_item['oauth'])
        {
            static::$_item['oauth'] = static::queryToArray(input::header('x-api-oauth'));
        }

        return static::$_item['oauth'];
    }

    //获取api请求数据
    static public function getApiArg()
    {
        if(!static::$_item['apiArg'])
        {
            $apiArg = input::header('x-api-arg');
            static::$_item['apiArg'] = static::queryToArray($apiArg);
        }

        return static::$_item['apiArg'];
    }

    //获取prism的key
    static public function getClientId()
    {
        if(!static::$_item['clientId'])
        {
            $apiArg = static::getApiArg();
            static::$_item['clientId'] = $apiArg['client_id'];
        }

        return static::$_item['clientId'];
    }

    //获取请求key属于的App的id
    static public function getProductId()
    {
        if(!static::$_item['productId'])
        {
            $apiArg = static::getApiArg();
            static::$_item['productId'] = $apiArg['product'];
        }

        return static::$_item['productId'];
    }

    //获取请求者ip
    static public function getCallerIp()
    {
        if(!static::$_item['callerId'])
        {
            $ips = input::header('x-caller-ip');
            static::$_item['callerIp'] = $ips;
        }

        return static::$_item['callerIp'];
    }

    //获取业务请求数据
    static public function getRequestParams()
    {
        if(!static::$_item['requestParams'])
        {
            $params = static::getParams();

            unset($params['method']);
            unset($params['timestamp']);
            unset($params['v']);
            unset($params['format']);
            unset($params['sign']);
            unset($params['sign_type']);

            static::$_item['requestParams'] = $params;
        }

        return static::$_item['requestParams'];

    }

    //获取系统参数
    static public function getSystemParams()
    {
        if(!static::$_item['systemParams'])
        {
            $params = static::getParams();

            $systemParams = [
                'method'    => $params['method'],
                'timestamp' => $params['timestamp'],
                'format'    => $params['format'],
                'v'         => $params['v'],
                'sign_type' => $params['sign_type'],
                'sign'      => $params['sign'],
                ];

            static::$_item['systemParams'] = $systemParams;
        }

        return static::$_item['systemParams'];
    }

    //获取method_id
    static public function getMethodId()
    {
        if(!static::$_item['methodId'])
        {
            $systemParams = static::getSystemParams();
            static::$_item['methodId'] = $systemParams['method'];
        }
        return static::$_item['methodId'];
    }

    //获取api的配置信息，包括api的类、版本号
    static public function getApiConf()
    {
        if(!static::$_item['apiConf'])
        {

            $methodId = static::getMethodId();
            $apis = config::get('apis.routes');
            static::$_item['apiConf'] = $apis[$methodId];
        }
        return static::$_item['apiConf'];
    }

    //一个工具方法，把http的query语句转化为数组
    static private function queryToArray($query) {

        if(empty($query))
            return array();

        $result = array();
        //string must contain at least one = and cannot be in first position
        if(strpos($query,'=')) {
            if(strpos($query,'?')!==false) {
                $q = parse_url($query);
                $query = $q['query'];
            }
        } else {
            return false;
        }

        foreach (explode('&', $query) as $couple) {
            list ($key, $val) = explode('=', $couple);
            $key = str_replace("+", " ", $key); // key里的+转换成空格
            $key = urldecode($key);
            $val = urldecode($val);
            $result[$key] = $val;
        }

        return empty($result) ? false : $result;
    }

}

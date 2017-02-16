<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_prism_response
{

    //组织返回数据的格式
    static public function send($params, $format = 'json', $httpCode = 200, $header = [])
    {
        return static::_out(['error'=>null, 'result'=>$params], $format, $httpCode, $header);
    }

    //组织返回格式的数据
    //这里把exception的类型带到了返回内容。方便在下级系统中直接抛出该异常
    static public function sendError($code, $message, $format = 'json', $exception = null)
    {
        $params = [
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
                'result' => null,
            ];
        if( isset($exception) )
        {
            $params['error']['exception'] = $exception;
        }
        return static::_out($params, $format);
    }

    static private function _out($params, $format = 'json', $httpCode = 200, $header = [])
    {
        if($format == 'xml')
        {
            $xml = kernel::single('site_utility_xml')->array2xml($params);

            return $xml;
        }else{
            return response::json($params, $httpCode, $header);
        }
        return null;
    }
}


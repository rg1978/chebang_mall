<?php

class system_middleware_checkApiSystemParams
{
    public function __construct()
    {
    }

    public function handle($request, Closure $next)
    {
        $params = base_prism_request::getParams();
        $apiConf = base_prism_request::getApiConf();

        $method    = $params['method'];
        $timestamp = $params['timestamp'];
        $format    = $params['format'];
        $v         = $params['v'];
        $sign_type = $params['sign_type'];
        $sign      = $params['sign'];

        try{
            if( !base_rpc_validate::isValidate($params) )
            {
                throw new LogicException(app::get('base')->_('签名错误'));
            }

            if( !( $format == 'json' || $format == 'xml' ) )
            {
                throw new LogicException(app::get('base')->_('返回格式设定必须是json或者xml'));
            }

            if( !is_numeric($timestamp) )
            {
                throw new LogicException(app::get('base')->_('时间格式错误（包含非数字的字符）'));
            }

            #if( time() - intval($timestamp) > 300 )
            #{
            #    throw new LogicException(app::get('base')->_('请求已超时'));
            #}

            if( $apiConf == null )
            {
                throw new LogicException(app::get('base')->_('找不到请求的api'));
            }

            if( !in_array($v, $apiConf['version']) )
            {
                throw new LogicException(app::get('base')->_('API版本不匹配'));
            }

        }
        catch(Exception $e)
        {
            $code = 'system.systemParams.checkFail';
            $exception = get_class($e);
            $message = $e->getMessage();

            $getRequestId = base_prism_request::getRequestId();
            //加入api日志
            $logData['apilog_id'] = $getRequestId ? $getRequestId : uniqid();
            $logData['msg_id'] = $params['msg_id'];
            $logData['worker'] = $params['method'];
            $logData['status'] = 'fail';
            $logData['result'] = ['error_msg'=>$message];
            $logData['params'] = [
                    'api_params' => $params,
                ];
            try
            {
                $this->apilogId = kernel::single('system_apilog')->create('response', $logData);
            }
            catch( Exception $e)
            {
                logger::info('apilog_data : '. var_export($logData,true));
            }

            return base_prism_response::sendError($code, $message, $format = 'json', $exception);
        }
        return $next($request);
    }

}


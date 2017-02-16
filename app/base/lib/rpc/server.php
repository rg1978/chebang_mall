<?php

class base_rpc_server
{


    public function process()
    {
    //  print_r(input::header('X-api-arg'));exit;

        $params       = base_prism_request::getRequestParams();
        $systemParams = base_prism_request::getSystemParams();
        $appInfo      = base_prism_request::getApiArg();
        $oauth        = base_prism_request::getOauthInfo();

        //加入api日志
        $logData['apilog_id'] = base_prism_request::getRequestId();
        $logData['msg_id'] = $params['msg_id'];
        $logData['worker'] = $systemParams['method'];
        $logData['params'] = [
                'request_ip'=>base_prism_request::getCallerIp(),
                'request_app_info' => $appInfo,
                'request_oauth_info' => $oauth,
                'api_params' => $params,
            ];
        try
        {
            $this->runtimeStart = microtime(true);
            $this->apilogId = kernel::single('system_apilog')->create('response', $logData);
        }
        catch( Exception $e)
        {
            logger::info('apilog_data : '. var_export($logData,true));
        }

        $apiConf = base_prism_request::getApiConf();
        $handler = $apiConf['uses'];
        list($class_name, $action_name) = explode('@', $handler);
        $class = new $class_name;

        try{

            //参数处理
            if( !method_exists( $class, 'getParams' ) )
            {
                throw new RuntimeException('获取参数列表失败');
            }
            $paramsInfos = $class->getParams();
            $params = $this->__oauthParamsPre($paramsInfos, $params, $oauth);

            apiUtil::paramsValidate($params, $paramsInfos, $handler);

            //预处理下params
            //转化下数据的格式，比如对数据结构fields进行转化和extends扩展等
            $params = apiUtil::pretreatment($params, $paramsInfos);

            //判断下方法是否存在
            if( !method_exists( $class, $action_name ) )
            {
                throw new RuntimeException('找不到方法 :' . $action_name);
            }

            $result = call_user_func([$class, $action_name], $params, $appInfo);
            try
            {
                $this->runtimeStop= microtime(true);
                $runtime = round(($this->runtimeStop - $this->runtimeStart) , 4);
                kernel::single('system_apilog')->update($this->apilogId, 'success', $result, $runtime);
            }
            catch( Exception $e)
            {
                logger::info('update_apilog_data success : '. var_export($result,true));
            }

            return base_prism_response::send($result);
        }
        catch(LogicException $e)
        {
            $errorMessage = $e->getMessage();
            $exceptionClass = get_class($e);
            $method    = $systemParams['method'];
            return $this->__sendError($method.".runtimeException", $errorMessage, $format, $exceptionClass);
        }
        catch(Exception $e)
        {
            if (config::get('app.debug'))
            {
                $errorMessage = $e->getMessage();
            }
            else
            {
                $errorMessage = '系统错误，服务暂不可用，请联系平台';
            }
            $exceptionClass = get_class($e);
            $method    = $systemParams['method'];
            return $this->__sendError($method.".runtimeException", $errorMessage, $format, $exceptionClass);
        }
    }

    private function __sendError($code , $errorMessage, $format, $exception)
    {
        try
        {
            $this->runtimeStop= microtime(true);
            $runtime = round(($this->runtimeStop - $this->runtimeStart) , 4);
            kernel::single('system_apilog')->update($this->apilogId, 'fail', ['message'=>$errorMessage,'exception'=>$exception], $runtime);
        }
        catch( Exception $e)
        {
            logger::info('update_apilog_data fail: '. var_export($errorMessage,true));
        }
        return base_prism_response::sendError($code, $errorMessage, $format, $exception);
    }

    /**
     * 处理oauth数据，合并到api参数中
     */
    private function __oauthParamsPre($paramsInfos, $params, $oauth)
    {
        foreach( ['shop_id', 'seller_id', 'user_id'] as $column)
        {
            if( $paramsInfos['params'][$column] && $oauth[$column] )
            {
                $params[$column] = ($column == 'shop_id') ?  intval($oauth[$column]) : intval($oauth['accountid']);
            }
        }

        return $params;
    }

}


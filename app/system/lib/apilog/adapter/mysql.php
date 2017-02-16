<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class system_apilog_adapter_mysql implements system_interface_apilog_adapter {

    private $__model = NULL;

    public function __construct()
    {
        $this->__model = app::get('system')->model('apilog');
    }

    /**
     * 创建API日志
     *
     * @param string $apiType api类型，API响应或者请求API
     * @param string $params 任务参数 响应的参数或者请求的参数
     *
     * @return bool
     */
    public function create($apiType, $params)
    {
        $time = time();
        $data = array(
            'msg_id' => $params['msg_id'],
            'apilog_id' => $params['apilog_id'],
            'worker' => $params['worker'],
            'api_type' => $apiType ? $apiType : 'response',
            'params' => serialize((array)$params['params']),
            'runtime' => $params['runtime'] ? $params['runtime'] : 0,
            'status' => $params['status'] ? $params['status'] : 'running',
            'result' => $params['result'] ? serialize((array)$params['result']) : null,
            'calltime' => $time,
            'last_modify' => $time
        );

        if( $this->__model->insert($data) )
        {
            return $params['apilog_id'];
        }

        return false;
    }

    /**
     * update 更新API日志
     *
     * @param int $apilog_id
     * @param string $status api响应或请求状态［成功或者失败］
     * @param string $result 响应需返回的数据，或请求返回的数据
     *
     */
    public function update($apilog_id, $status, $result, $runtime)
    {
        $time = time();
        $data = [
            'result' => serialize((array)$result),
            'status' => $status,
            'runtime' => $runtime,
            'last_modify' => $time,
        ];

        return $this->__model->update($data, ['apilog_id'=>$apilog_id]);
    }

    /**
     * 获取一条api日志信息
     *
     * @param int $apilog_id
     * @param string $fields 需要返回的字段
     */
    public function get($apilog_id, $fields)
    {
        return $this->__model->getRow($fields, ['apilog_id'=>$apilog_id]);
    }
}


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author bryant.yan@gmail.com
 */

interface system_interface_apilog_adapter{

    /**
     * create 创建API日志
     *
     * @param string $apiType api类型，API响应或者请求API
     * @param string $params 任务参数 响应的参数或者请求的参数
     */
    public function create($apiType, $params);

    /**
     * update 更新API日志
     *
     * @param int $apilog_id
     * @param string $status api响应或请求状态［成功或者失败］
     * @param string $result 响应需返回的数据，或请求返回的数据
     *
     */
    public function update($apilog_id, $status, $result, $runtime);

    /**
     * 获取一条api日志信息
     *
     * @param int $apilog_id
     * @param string $fields 需要返回的字段
     */
    public function get($apilog_id, $fields);
}



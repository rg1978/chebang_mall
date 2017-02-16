<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author bryant.yan@gmail.com
 */

interface system_interface_queue_adapter{

    /**
     * publish新任务
     *
     * @param string $exchange_name exchange名称
     * @param string $params 任务参数
     * @param string $routing_key 路由key
     * @return bool 是否成功
     */
    public function publish($queueName, $queueData);

    public function get($queueName);

    public function purge($queueName);

    public function ack($queueName, $queueData);

    public function is_end($queueName);
}



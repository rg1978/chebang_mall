<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
//执行失败队列

use base_support_arr as Arr;

class system_queue_message_failed extends system_queue_message_abstract implements system_interface_queue_message{

    public function __construct($failedQueue, $queueData, $queueName, $id)
    {
        $this->failedQueue = $failedQueue;
        $this->queueData = $queueData;
        $this->queueName = $queueName;
        $this->contentArr = json_decode($queueData, true);
        $this->id = $id;
    }


    public function fire()
    {
        parent::fire();

        $this->ack();

        return true;
    }

    /**
     * 确认队列消费
     *
     * @return void
     */
    public function ack()
    {
        $this->failedQueue->delete(['id'=>$this->id]);
    }
}

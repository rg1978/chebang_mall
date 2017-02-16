<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class system_queue_message_mysql extends system_queue_message_abstract implements system_interface_queue_message{


    public function __construct($mysqlQueue, $queueName, $queueData)
    {
        $this->mysqlQueue = $mysqlQueue;
        $this->queueName = $queueName;
        $this->queueData = $queueData;
        $this->contentArr = $queueData;
    }

    public function fire()
    {
        parent::fire();

        $this->ack();

        return true;
    }

    public function ack()
    {
        return $this->mysqlQueue->ack($this->queueName, $this->queueData['id']);
    }
}

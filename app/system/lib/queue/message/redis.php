<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class system_queue_message_redis extends system_queue_message_abstract implements system_interface_queue_message{

    public function __construct($redisQueue, $queueData, $queueName)
    {
        $this->redis = $redisQueue;
        $this->queueData = $queueData;
        $this->queueName = $queueName;
        $this->contentArr = json_decode($queueData, true);
    }

    /**
     * 执行队列
     */
    public function fire()
    {
        try
        {
            parent::fire();
            $this->ack();
        }
        catch( Exception $e )
        {
            $objectWorker = kernel::single($this->contentArr['worker']);
            if( method_exists($objectWorker, 'getTries') )
            {
                $this->setTries( $objectWorker->getTries() );
            }

            if( method_exists($objectWorker, 'getDelayTime') )
            {
                $this->setDelayTime( $objectWorker->getDelayTime($this->attempts()) );
            }

            //如果!$this->tries表示不能重试，执行失败则直接进入失败队列。 失败超过重试次数进入失败队列
            if( !$this->tries || $this->attempts() > $this->tries )
            {
                $this->ack();
                $message = $e->getMessage();
                parent::queueFailed($message);
            }
            else
            {
                //放入到延时队列
                $this->release($this->delayTime);
            }
        }
        return true;
    }

    /**
     *  获取当前队列执行次数
     *
     * @return int
     */
    public function attempts()
    {
        return $this->contentArr['attempts'];
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->ack();

        $this->redis->release($this->queueName, $this->queueData, $delay, $this->attempts() + 1);
    }

    /**
     * 确认队列消费
     *
     * @return void
     */
    public function ack()
    {
        $attempts = $this->attempts() + 1;
        $this->contentArr['attempts'] = $attempts;
        $this->redis->ack($this->queueName, json_encode($this->contentArr));
    }
}

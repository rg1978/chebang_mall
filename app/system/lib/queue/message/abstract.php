<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class system_queue_message_abstract {

    /**
     *  默认队列重试次数
     */
    public $tries = 5;

    /**
     * 默认延时队列自动重试时间 秒/单位
     *
     * int
     */
    public $delayTime = 300;

    /**
     * 取出队列的数据
     */
    protected $queueData;

    /**
     * 队列名称
     */
    protected $queueName;

    /**
     * 解析后的队列数据
     */
    protected $contentArr;

    /**
     * 执行队列
     */
    public function fire()
    {
        $params = $this->contentArr['params'];
        $worker = $this->contentArr['worker'];


        $objectWorker = kernel::single($this->contentArr['worker']);
        if ($objectWorker instanceof base_interface_task)
        {
            call_user_func_array(array($objectWorker, 'exec'), array($params));
        }
        return true;
    }

    /**
     * 队列执行失败超出重试次数，将改队列插入到一个失败队列的数据表中
     * 并且出发队列执行失败事件
     *
     * @param array $failedMessage 失败原因
     */
    public function queueFailed($failedMessage)
    {
        $mdlQueueFailed = app::get('system')->model('queue_failed');
        $insetData = [
            'queue_name' => $this->queueName,
            'data' => is_string($this->queueData) ? $this->queueData : json_encode($this->queueData),
            'create_time' => time(),
            'reason' => $failedMessage,
        ];
        $queueFailedId = $mdlQueueFailed->insert($insetData);

        event::fire('queue.failed', ['queue_failed_id'=>$queueFailedId]);
        return true;
    }

    /**
     * 获取队列数据
     */
    public function getQueueData()
    {
        return $this->queueData;
    }

    /**
     * 获取队列数据(解析后的)
     */
    public function getQueueBody()
    {
        return $this->contentArr;
    }

    /**
     * 获取队列名称
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * 设置延时队列自动重试时间
     *
     * @param $delay int 延时时间秒seconds
     */
    public function setDelayTime($delay)
    {
        $this->delayTime = $delay;
    }

    /**
     * 设置队列重试次数
     *
     * @param $tries int 可执行重试次数
     */
    public function setTries($tries)
    {
        $this->tries = $tries;
    }
}

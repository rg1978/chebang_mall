<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use base_support_arr as Arr;

class system_queue_adapter_redis implements system_interface_queue_adapter {

    /**
     * 创建执行队列的有效时间
     *
     * @var int|null
     */
    protected $expire = 3600;

    /**
     * 创建一个队列任务
     *
     * @param  string  $queueName 队列标示
     * @param  array   $data 执行队列参数
     * @return mixed
     */
    public function publish($queueName, $queueData )
    {
        return $this->pushRaw($this->createPayload($queueData), $queueName);
    }

    /**
     * 将队列保存到redis
     *
     * @param  string  $payload
     * @param  string  $queue
     * @return mixed
     */
    public function pushRaw($payload, $queueName )
    {
        redis::scene('queue')->rpush($queueName, $payload);

        return Arr::get(json_decode($payload, true), 'id');
    }

    /**
     * 获取一个队列任务ID
     *
     * @param string $queueName
     * @return mixed 队列任务数据
     */
    public function get($queueName)
    {
        if (! is_null($this->expire) )
        {
            $this->migrateAllExpiredJobs($queueName);
        }

        $objectRedis = redis::scene('queue');
        $objectRedis->loadScripts('queueGet');

        $queueData = $objectRedis->queueGet($queueName, 'queue:'.$queueName.':reserved', time() + $this->expire);
        if( ! empty($queueData) )
        {
            return new system_queue_message_redis($this, $queueData, $queueName);
        }

        return false;
    }

    /**
     * 确认消息已经被消费.
     *
     * @param  string  $queueData
     * @return void
     */
    public function ack($queueName, $queueData)
    {
        return redis::scene('queue')->zrem($queueName.':reserved', $queueData);
    }

    /**
     * 清空一个队列
     *
     * @param string $queue
     */
    public function purge($queueName)
    {
        redis::scene('queue')->ltrim($queueName,-1,0);
    }

    public function is_end($queueName)
    {
        $len = redis::scene('queue')->llen($queueName);
        $reservedLen = redis::scene('queue')->zcount($queueName.':reserved', '-inf', time());
        $delayedLen = redis::scene('queue')->zcount($queueName.':delayed', '-inf', time());
        return ($len > 0 || $reservedLen > 0 || $delayedLen > 0 ) ? false : true;
    }

    /**
     * 将所有延时队列或者处理超时的队列重新加入到队列中
     *
     * @param  string  $queue
     * @return void
     */
    protected function migrateAllExpiredJobs($queueName)
    {
        $this->migrateExpiredJobs($queueName.':delayed', $queueName);

        $this->migrateExpiredJobs($queueName.':reserved', $queueName);
    }

    /**
     * 将延时队列或者处理超时的队列重新加入到执行队列中
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    public function migrateExpiredJobs($from, $to)
    {
        $from = $from;
        $to = 'queue:'.$to;

        $objectReids = redis::scene('queue');
        $objectReids->loadScripts('queueMigrate');

        $v = $objectReids->queueMigrate($from, $to, time());


        return $v;
    }

    /**
     * 获取失效的队列
     *
     * @param  \Predis\Transaction\MultiExec  $transaction
     * @param  string  $from
     * @param  int  $time
     * @return array
     */
    protected function getExpiredJobs($transaction, $from, $time)
    {
        return $transaction->zrangebyscore($from, '-inf', $time);
    }

    /**
     * 删除失效的队列
     *
     * @param  \Predis\Transaction\MultiExec  $transaction
     * @param  string  $from
     * @param  int  $time
     * @return void
     */
    protected function removeExpiredJobs($transaction, $from, $time)
    {
        $transaction->multi();

        $transaction->zremrangebyscore($from, '-inf', $time);
    }

    /**
     *  存储一个新的延时队列
     *
     * @param  int  $delay
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $data = '', $queue = null)
    {
        $payload = $this->createPayload($data);

        redis::scene('queue')->zadd($queue.':delayed', time() + (int)$delay, $payload);

        return Arr::get(json_decode($payload, true), 'id');
    }

    /**
     * Release a reserved job back onto the queue.
     *
     * @param  string  $queue
     * @param  string  $payload
     * @param  int  $delay
     * @param  int  $attempts
     * @return void
     */
    public function release($queue, $payload, $delay, $attempts)
    {
        $payload = $this->setMeta($payload, 'attempts', $attempts);

        redis::scene('queue')->zadd($queue.':delayed', time() + $delay, $payload);
    }

    /**
     * 将失效的队列和延时队列加入到执行队列中
     *
     * @param  \Predis\Transaction\MultiExec  $transaction
     * @param  string  $to
     * @param  array  $jobs
     * @return void
     */
    protected function pushExpiredJobsOntoNewQueue($transaction, $to, $jobs)
    {
        call_user_func_array([$transaction, 'rpush'], array_merge([$to], $jobs));
    }

    /**
     * 创建队列执行参数，数组转为字符串
     *
     * @param  mixed   $data
     * @return string
     */
    protected function createPayload($data = '')
    {
        $payload = $this->setMeta($data, 'id', $this->getRandomId());
        $payload = $this->setMeta($payload, 'create_time', time());

        return $this->setMeta($payload, 'attempts', 0);
    }

    /**
     * Get a random ID string.
     *
     * @return string
     */
    protected function getRandomId()
    {
        return str_random(32);
    }

    /**
     * 在队列执行参数中追加其他参数
     *
     * @param string $payload 队列参数
     * @param string $key
     * @param string $value
     */
    protected function setMeta($payload, $key, $value)
    {
        if( ! is_array($payload) )
        {
            $payload = json_decode($payload, true);
        }

        return json_encode(Arr::set($payload, $key, $value));
    }

    /**
     * Get the expiration time in seconds.
     *
     * @return int|null
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Set the expiration time in seconds.
     *
     * @param  int|null  $seconds
     * @return void
     */
    public function setExpire($seconds)
    {
        $this->expire = $seconds;
    }
}


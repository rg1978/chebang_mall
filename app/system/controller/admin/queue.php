<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author afei, bryant
 */


class system_ctl_admin_queue extends desktop_controller {

    var $workground = 'system.workground.setting';


    public $limit = 20;

    function index() {
        $params = array (
            'title' => app::get('system')->_('队列管理'),
        );

        $queue_controller_name = system_queue::get_driver_name();
        $support_queue_controller_name = 'system_queue_adapter_mysql';

        if ($queue_controller_name == $support_queue_controller_name)
        {
            return $this->finder('system_mdl_queue_mysql', $params);
        }
        else
        {
            if( $queue_controller_name == 'system_queue_adapter_redis' )
            {
                $pagedata = $this->__getRedisQueue();
                $pagedata['type'] = $_GET['type'];
                return $this->page('system/admin/queue/redis.html', $pagedata);
            }
            else
            {
                $pagedata['queue_controller_name'] = $queue_controller_name;
                $pagedata['support_queue_controller_name'] = $support_queue_controller_name;
                return $this->page('system/admin/queue.html', $pagedata);
            }
        }
    }

    private function __getRedisQueue()
    {
        $queueList = config::get('queue.queues');

        $objectReids = redis::scene('queue');
        $total = [];
        $waitTotal = 0;
        $reservedTotal = 0;
        $delayedTotal = 0;

        $RedisQueue = kernel::single('system_queue_adapter_redis');
        //默认执行队列的有效时间
        $expire = $RedisQueue->getExpire();
        foreach( $queueList as $queueName => $row )
        {
            $waitLen = $objectReids->llen($queueName);
            $queueList[$queueName]['wait']['len'] = $waitLen;
            $waitTotal += $waitLen;
            $waitData = $objectReids->lrange($queueName,0, 10);

            if( $waitData )
            {
                $waitQueueData = $waitQueueData ? array_merge($waitQueueData, $waitData) : $waitData;
            }

            $activeLen = $objectReids->zcount($queueName.":reserved", '-inf', '+inf');
            $queueList[$queueName]['active']['len'] = $activeLen;
            $activeTotal += $activeLen;

            $activeData = $objectReids->zrange($queueName.":reserved",0, 9, "WITHSCORES");
            if( $activeData )
            {
                $activeQueueData = $activeQueueData ? array_merge($activeQueueData, $activeData) : $activeData;
            }

            $delayedLen = $objectReids->zcount($queueName.":delayed", '-inf', '+inf');
            $queueList[$queueName]['delayed']['len'] = $delayedLen;
            $delayedTotal += $delayedLen;
            $delayedData = $objectReids->zrange($queueName.":delayed", 0, 9, 'WITHSCORES');
            if( $delayedData )
            {
                $delayedQueueData = $delayedQueueData ? array_merge($delayedQueueData, $delayedData) : $delayedData;
            }
        }

        foreach( $waitQueueData as $key=>$row )
        {
            $row = json_decode($row, 1);
            $waitQueueDataArr[] = $row;
        }

        foreach( $delayedQueueData as $key=>$row )
        {
            $key = json_decode($key, 1);
            $key['delayed_time'] = $row;
            $delayedQueueDataArr[] = $key;
        }

        foreach( $activeQueueData as $key=>$row )
        {
            $key = json_decode($key, 1);
            $key['expire_time'] = $row;
            $activeQueueDataArr[] = $key;
        }

        $total['wait'] = $waitTotal;
        $total['delayed'] = $delayedTotal;
        $total['active'] = $activeTotal;
        $queueData['wait'] = $waitQueueDataArr;
        $queueData['delayed'] = $delayedQueueDataArr;
        $queueData['active'] = $activeQueueDataArr;
        return ['queuelist'=>$queueList, 'total'=>$total, 'queueData'=>$queueData];
    }

    //查看指定队列数据
    public function showRedisQueueParams($params, $id)
    {
        $pagedata['id']= $id;
        $pagedata['params']= urldecode($params);
        return view::make('system/admin/queue/redisParams.html', $pagedata);
    }

    public function delRedisQueue($queueType, $value)
    {
        $this->begin('?app=system&ctl=admin_queue&act=index&type='.$queueType);
        $value = json_decode(urldecode($value),true);
        if( isset($value['expire_time']))
        {
            unset($value['expire_time']);
        }

        if( isset($value['delayed_time']))
        {
            unset($value['delayed_time']);
        }

        $value = json_encode($value);
        $objectReids = redis::scene('queue');
        if( $queueType == 'wait' )
        {
            $queueName = json_decode($value,1)['queue_name'];
            $result = $objectReids->lrem($queueName ,1,$value);
        }
        elseif( $queueType == 'active' )
        {
            $queueName = json_decode($value,1)['queue_name'].":reserved";
            $result = $objectReids->zrem($queueName ,1,$value);
        }
        else
        {
            $queueName = json_decode($value,1)['queue_name'].":delayed";
            $result = $objectReids->zrem($queueName ,1,$value);
        }

        $this->end(true, app::get('system')->_('操作成功'));
    }
}

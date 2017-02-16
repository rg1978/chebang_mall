<?php
/**
 * 将事件的listeners分发到对于执行的队列
 */
class system_tasks_distrEvents extends base_task_abstract implements base_interface_task {

    public function exec($params=null)
    {
        $queueParams['eventParams'] = $params['eventParams'];
        $queueParams['eventName'] = $params['eventName'];
        foreach( $params['listeners'] as $key=>$listener )
        {
            $queue = $params['queues'][$listener];
            $queueParams['listener'] = $listener;
            system_queue::instance()->publish($queue, $queue, $queueParams);
        }
    }

}

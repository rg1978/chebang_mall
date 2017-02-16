<?php
/**
 * 默认执行事件监听类
 */
class system_tasks_events extends base_task_abstract implements base_interface_task{

    public function exec($params=null)
    {
        event::push($params['eventName'], $params['listener'], $params['eventParams']);
    }
}



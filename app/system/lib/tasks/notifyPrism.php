<?php
/**
 * 推送prism消息队列
 * 为什么不用默认的事件监听类?
 *
 * 1 prism推送需要判断是否需要推送
 * 2 消息有顺序，因此推送消息队列只能单线程进行
 * 3 prism消息对于实时性要求较高，可以单独出一个队列类型进行执行（todo）
 */
class system_tasks_notifyPrism extends base_task_abstract implements base_interface_task {

    public function exec($params=null)
    {
        if( config::get('prism.prismNotify') )
        {
            event::push($params['eventName'], $params['listener'], $params['eventParams']);
        }
    }
}



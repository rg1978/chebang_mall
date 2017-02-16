<?php

class system_events_listeners_testAsync implements base_events_interface_queue {

    public function test($data)
    {
        $str = '测试事件异步执行';
        echo $str;
        logger::info('events：async'.$str);
        error_log(var_export($str,1)."\n",3,DATA_DIR.'/log.log');
    }
}

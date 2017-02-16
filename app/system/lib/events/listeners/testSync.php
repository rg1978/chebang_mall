<?php

class system_events_listeners_testSync {


    public function handle($data)
    {
        echo '测试事件同步执行参数'."\n";
        print_r($data);

        $str = '测试事件同步执行完成';
        echo $str."\n\n";

        return true;
    }
}

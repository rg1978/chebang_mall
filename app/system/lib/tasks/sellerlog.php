<?php
// 插入商家操作日志
class system_tasks_sellerlog extends base_task_abstract implements base_interface_task{
    public function exec($params=null)
    {
        $queue_params = array(
            'seller_userid'   => $params['seller_userid'],
            'seller_username' => $params['seller_username'],
            'shop_id'         => $params['shop_id'],
            'created_time'    => $params['created_time'],
            'memo'            => $params['memo'],
            'router'          => $params['router'],
            'ip'              => $params['ip'],
        );
        app::get('system')->model('seller_log')->insert($queue_params);
    }
}



<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_tasks_operatorday extends base_task_abstract implements base_interface_task
{

    public function exec($params=null)
    {

        if(!$params)
        {
            $params = array(
                'time_start'=>strtotime(date('Y-m-d 00:00:00', strtotime('-1 day'))),
                'time_end'=>strtotime(date('Y-m-d 23:59:59', strtotime('-1 day'))),
                'time_insert'=>strtotime(date('Y-m-d', strtotime('-1 day'))),
            );
        }
        echo date('Y-m-d H:i:s',$params['time_start']).'/'.date('Y-m-d H:i:s',$params['time_end'])."\n";
        if(kernel::single('sysstat_operatorshop_taskdata')->exec($params))
        {
            return true;
        }
    }


}

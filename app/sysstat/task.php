<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_task{

    public function post_install($options)
    {
      //if($dbver['dbver'] < 0.2)
      //{
      //    $timeStart = '2015-01-01';
      //    $timeEnd = date('Y-m-d', strtotime('-1 day'));
      //    kernel::single('sysstat_command_sysstatexec')->command_execManager($timeStart,$timeEnd);
      //}
    }

    public function post_update($dbver)
    {
        if($dbver['dbver'] < 0.2)
        {
            $timeStart = '2015-01-01';
            $timeEnd = date('Y-m-d', strtotime('-1 day'));
            kernel::single('sysstat_command_sysstatexec')->command_execManager($timeStart,$timeEnd);
        }
    }

}


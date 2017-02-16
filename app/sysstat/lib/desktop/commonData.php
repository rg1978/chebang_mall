<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_commonData
{
     /**
     * @brief  根据时间类型获取时间范围
     * $timeType string yesterday,before,week,month
     * @return array 时间范围
     */
    public function getTimeRangeByType($timeType)
    {
        switch ($timeType) {
            case 'yesterday':
                return array(
                    'time_start'=>date('Y-m-d 00:00:00', strtotime('-1 day')),
                    'time_end'=>date('Y-m-d 23:59:59', strtotime('-1 day'))
                );
                break;
            case 'beforeday':
                return array(
                    'time_start'=>date('Y-m-d 00:00:00', strtotime('-2 day')),
                    'time_end'=>date('Y-m-d 23:59:59', strtotime('-2 day'))
                );
                break;
            case 'week':
                return array(
                    'time_start'=>date('Y-m-d 00:00:00', strtotime('-7 day')),
                    'time_end'=>date('Y-m-d 23:59:59', strtotime('-1 day'))
                );
                break;
            case 'month':
                return array(
                    'time_start'=>date('Y-m-d 00:00:00', strtotime('-30 day')),
                    'time_end'=>date('Y-m-d 23:59:59', strtotime('-1 day'))
                );
                break;
        }
    }


    /**
     * @brief  获取时间数组[2015-03-03,2015-01-05] array
     * $timeStart 查询的开始时间 2015-03-01
     * $timeEnd 查询的结束时间2015-03-03
     * @return array
     */
    public function getCategories($timeStart,$timeEnd,$selectTimeType=null)
    {

        $rangeTime = $timeEnd-$timeStart;
        $rangeDay = ceil($rangeTime/86400);

        for ($i=0; $i <$rangeDay ; $i++)
        {
            $timechange[] = date('Y-m-d',$timeStart+86400*$i);
        }
        if($selectTimeType)
        {
            if($selectTimeType=='byday')
            {
                $timedatainfo = $timechange;
            }
            if($selectTimeType=='byweek')
            {
                $timedatainfo = array_chunk($timechange, 7);
            }
            if($selectTimeType=='bymonth')
            {
                $timedatainfo = array_chunk($timechange, 30);
            }
            //echo '<pre>';print_r($timedatainfo);exit();
            $timedata = $this->__getTimeInfo($timedatainfo,$selectTimeType);
        }
        else
        {
            $timedata = $timechange;
        }
            return $timedata;
    }

    private function __getTimeInfo($timedata,$selectTimeType=null)
    {
        if($selectTimeType=='byday')
        {
            return $timedata;
        }
        else
        {
            foreach ($timedata as $key => $value)
            {
                $data[$key] = $value[0].'/'.$value[count($value)-1];
            }
        }
        return $data;
    }


}

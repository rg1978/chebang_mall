<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class  sysstat_ctl_admin_operating extends desktop_controller
{
    public function index()
    {
        //kernel::single('sysstat_tasks_operatorday')->exec();exit();
        $params = array(
            'time_start'=>date('Y-m-d 00:00:00', strtotime('-1 day')),
            'time_end'=>date('Y-m-d 23:59:59', strtotime('-1 day'))
        );
        $pagedata['time_start'] = $params['time_start'];
        $pagedata['time_end'] = $params['time_end'];

        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('sysstat/admin/report/operat.html',$pagedata);
    }

    //报表
    public function analysis()
    {
        $data = input::get();
        $pagedata = kernel::single('sysstat_desktop_tradeData')->getCommonData($data);
        $pagedata['operatTradeData'] = kernel::single('sysstat_desktop_tradeData')->getOperatData($data);
        $pagedata['operatUserData'] = kernel::single('sysstat_desktop_userListData')->getUserOperatData($data);
        //echo '<pre>';print_r($pagedata);exit();
        return view::make('sysstat/admin/report/operatAnalysis.html',$pagedata);
    }

    //异步请求获取的数据
    public function ajaxData()
    {
        $data = input::get();
        //echo '<pre>';print_r($data);exit();
        try
        {
            $pagedata = kernel::single('sysstat_desktop_tradeData')->getCommonData($data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $pagedata['operatTradeData'] = kernel::single('sysstat_desktop_tradeData')->getOperatData($data);
        $pagedata['operatUserData'] = kernel::single('sysstat_desktop_userListData')->getUserOperatData($data);
        return  response::json($pagedata) ;
    }
     //异步请求时间获取的数据
    public function ajaxTimeData()
    {
        $data = input::get();
        //echo '<pre>';print_r($data);exit();
        try
        {
            $pagedata = kernel::single('sysstat_desktop_tradeData')->getCommonData($data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $pagedata['operatTradeData'] = kernel::single('sysstat_desktop_tradeData')->getOperatData($data);
        $pagedata['operatUserData'] = kernel::single('sysstat_desktop_userListData')->getUserOperatData($data);
        return  response::json($pagedata) ;
    }

}

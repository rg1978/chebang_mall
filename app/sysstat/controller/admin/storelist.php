<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class  sysstat_ctl_admin_storelist extends desktop_controller
{

    /**
     * @brief  通过iframe把时间参数传进去
     *
     * @return
     */
    public function index()
    {
        //kernel::single('sysstat_tasks_operatorday')->exec();exit();


        $params = array(
            'time_start'=>date('Y-m-d 00:00:00', strtotime('-1 day')),
            'time_end'=>date('Y-m-d 23:59:59', strtotime('-1 day'))
        );
        $pagedata['time_start'] = $params['time_start'];
        $pagedata['time_end'] = $params['time_end'];
        $pagedata['dataType'] = 'num';

        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('sysstat/admin/report/storelist.html',$pagedata);
    }

    //报表
    public function storeListAnalysis()
    {
        $data = input::get();
        $pagedata = kernel::single('sysstat_desktop_storeListData')->getCommonData($data);
        $pagedata['dataType'] = $data['dataType'];
        //echo '<pre>';print_r($pagedata);exit();
        return view::make('sysstat/admin/report/storeListAnalysis.html',$pagedata);
    }
    //数据显示
    public function dataShow()
    {
        return $this->finder('sysstat_mdl_desktop_stat_shop',array(
            'use_buildin_delete' => false,
            'use_buildin_filter'=>true,
            'use_buildin_export' => true,
            'title' => app::get('sysshop')->_('店铺排行'),
        ));
    }

     //异步请求获取的数据
    public function ajaxData()
    {
        $data = input::get();
        //echo '<pre>';print_r($data);exit();
        try
        {
            $pagedata = kernel::single('sysstat_desktop_storeListData')->getCommonData($data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $pagedata['dataType'] = $data['dataType'];
        //echo '<pre>';print_r($pagedata);exit();
        return  response::json($pagedata) ;
    }


     //异步请求时间获取的数据
    public function ajaxTimeData()
    {
        $data = input::get();
        //echo '<pre>';print_r($data);exit();
        $pagedata = kernel::single('sysstat_desktop_storeListData')->getCommonData($data);
        $pagedata['dataType'] = $data['dataType'];
        //echo '<pre>';print_r($pagedata);exit();
        return  response::json($pagedata) ;
    }



}

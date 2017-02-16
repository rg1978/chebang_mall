<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class  sysstat_ctl_admin_tradeData extends desktop_controller
{

    public $selectTimeType = [
        'byday' => '86400',
        'byweek' => '604800',
        'bymonth' => '2952000'
    ];
    public $selectStatus = [
        'byday' => fasle,
        'byweek' => false,
        'bymonth' => false
    ];

    public function index()
    {
        //kernel::single('sysstat_tasks_operatorday')->exec();exit();
        return $this->finder('sysstat_mdl_desktop_trade_statics',array(
            'use_buildin_delete' => false,
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'use_buildin_export' => true,
            'title' => app::get('sysshop')->_('平台交易数据'),
        ));
    }

    /**
     * 桌面订单汇总显示
     * @param null
     * @return null
     */
    public function _views()
    {
        $sub_menu = array(
            0=>array('label'=>app::get('sysstat')->_('全部'),'optional'=>false,'filter'=>array('stats_trade_from'=>'all')),
            1=>array('label'=>app::get('sysstat')->_('pc端'),'optional'=>false,'filter'=>array('stats_trade_from'=>'pc')),
            2=>array('label'=>app::get('sysstat')->_('触屏端'),'optional'=>false,'filter'=>array('stats_trade_from'=>'wap')),
        );
        if(isset($_GET['optional_view'])) $sub_menu[$_GET['optional_view']]['optional'] = false;
        foreach($sub_menu as $k=>$v)
        {
            if($v['optional']==false)
            {
                $show_menu[$k] = $v;
                if(is_array($v['filter']))
                {
                    $v['filter'] = array_merge(array(),$v['filter']);
                }
                else
                {
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                $show_menu[$k]['href'] = '?app=sysstat&ctl=admin_tradeData&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }
            elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view'])
            {
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

    /**
     * @brief  销售统计——交易数据报表
     *
     * @return
     */

    public function dataAnalysis()
    {
        $postdata = input::get();
        if(is_null($postdata['timeRange']))
        {
            $params = array(
                'time_start'=>date('Y-m-d 00:00:00', strtotime('-1 day')),
                'time_end'=>date('Y-m-d 23:59:59', strtotime('-1 day'))
            );
            $postdata['time_start'] = $params['time_start'];
            $postdata['time_end'] = $params['time_end'];
        }

        $pagedata['time_start'] = $postdata['time_start'];
        $pagedata['time_end'] = $postdata['time_end'];
        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('sysstat/admin/report/trade.html',$pagedata);
    }
    //报表
    public function analysis()
    {
        $data = input::get();
        $pagedata = kernel::single('sysstat_desktop_tradeData')->getCommonData($data);
        //echo '<pre>';print_r($pagedata);exit();
        return view::make('sysstat/admin/report/analysis.html',$pagedata);
    }

    //运营平台首页报表的显示
    public function commonAnalysis()
    {
        $data = input::get();

        $pagedata = kernel::single('sysstat_desktop_tradeData')->getCommonData($data);
        //echo '<pre>';print_r($pagedata);exit();
        return view::make('sysstat/admin/report/commomanalysis.html',$pagedata);
    }
    //异步请求获取的数据
    public function ajaxData()
    {
        $data = input::get();

        try
        {
            $pagedata = kernel::single('sysstat_desktop_tradeData')->getCommonData($data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            //echo '<pre>';print_r($msg);exit();
            return $this->splash('error',null,$msg);
        }
    //echo '<pre>';print_r($pagedata);exit();
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
            //echo '<pre>';print_r($msg);exit();
            return $this->splash('error',null,$msg);
        }
        return  response::json($pagedata) ;
    }

    //异步加载时间跨度选择器
    public function ajaxTimeType()
    {
        $postdata = input::get();
        $timeStart = strtotime($postdata['time_start']);
        $timeEnd = strtotime($postdata['time_end']);
        $poorTime = $timeEnd-$timeStart;//时间差
        if($poorTime<$this->selectTimeType['byweek'])
        {
            $this->selectStatus['byday'] = true;
        }
        elseif ($poorTime<$this->selectTimeType['bymonth'])
        {
            $this->selectStatus['byday'] = true;
            $this->selectStatus['byweek'] = true;
        }
        elseif ($poorTime>$this->selectTimeType['bymonth'])
        {
            $this->selectStatus['byday'] = true;
            $this->selectStatus['byweek'] = true;
            $this->selectStatus['bymonth'] = true;
        }
        $pagedata = $this->selectStatus;
        //echo '<pre>';print_r($pagedata);exit();
        return  response::json($pagedata) ;
    }
}

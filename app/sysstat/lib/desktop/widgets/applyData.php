<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_widgets_applyData implements desktop_interface_widget
{
    var $order = 1;
    function __construct()
    {
        $this->app = app::get('sysstat');
    }


    function get_title(){

        return app::get('sysstat')->_("店铺");

    }

    function get_html()
    {

       $params = array(
                'time_start'=>strtotime(date('Y-m-d 00:00:00', strtotime('-1 day'))),
                'time_end'=>strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')))
            );
        $mdlDesktopUserStat = app::get('sysstat')->model('desktop_stat_user');
        $filter = array(
            'createtime|bthan'=>$params['time_start'],
            'createtime|lthan'=>$params['time_end']
        );

        $fileds = 'shopnum,shopaccount';
        $data = $mdlDesktopUserStat->getList($fileds,$filter);
        $pagedata['applydata'] = $data[0];
        //echo '<pre>';print_r($data);exit();
        return view::make('sysstat/desktop/widgets/applydata.html', $pagedata)->render();
    }
    function get_className()
    {
        
          return " valigntop";
    }
    function get_width()
    {
          
          return "l-1";
        
    }

}
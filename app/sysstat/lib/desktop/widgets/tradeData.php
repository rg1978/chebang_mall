<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_widgets_tradeData implements desktop_interface_widget
{

    var $order = 1;
    function __construct($app)
    {
        $this->app = app::get('sysstat');
    }

    function get_title(){

        return app::get('sysstat')->_("交易");

    }

    function get_html()
    {

        $params = array(
                'time_start'=>strtotime(date('Y-m-d 00:00:00', strtotime('-1 day'))),
                'time_end'=>strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')))
            );
        $mdlDesktopTradeStat = app::get('sysstat')->model('desktop_trade_statics');
        $filter = array(
            'createtime|bthan'=>$params['time_start'],
            'createtime|lthan'=>$params['time_end'],
            'stats_trade_from'=>'all'
        );

        $fileds = 'new_trade,refunds_num';
        $data = $mdlDesktopTradeStat->getList($fileds,$filter);
        $pagedata['tradedata'] = $data[0];
        //echo '<pre>';print_r($data);exit();
        return view::make('sysstat/desktop/widgets/tradeaccount.html', $pagedata)->render();
    }
    function get_className()
    {
        
          return " valigntop exstatistics";
    }
    function get_width()
    {
          
          return "l-1";
        
    }

}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_widgets_promotionData implements desktop_interface_widget
{
    var $order = 1;
    function __construct()
    {
        $this->app = app::get('sysstat');
    }


    function get_title(){

        return app::get('sysstat')->_("营销");

    }

    function get_html()
    {

        return view::make('sysstat/desktop/widgets/promotiondata.html', $pagedata)->render();
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
<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topm_site_view_helper {

    public function function_header($params, $template, $pagedata)
    {
        $appleDesktop = app::get('sysconf')->getConf('sysconf_setting.wapmac_logo');
        $wapTitle = app::get('sysconf')->getConf('sysconf_setting.wap_name');
        $pagedata['appleDesktop'] = $appleDesktop;
        $pagedata['wapTitle'] = $wapTitle;
        //echo '<pre>';print_r($pagedata);exit();
        return view::make('topm/common/header.html',$pagedata)->render();
    }

    public function function_footer($params, $template)
    {
        $html = app::get('sysconf')->getConf('sysconf_setting.wap_description');
        return $html;
    }

}//结束


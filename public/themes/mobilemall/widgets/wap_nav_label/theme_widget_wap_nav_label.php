<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_wap_nav_label(&$setting){
    $theme_dir = kernel::get_themes_host_url().'/'.theme::getThemeName();
    $setting['themeUrl'] = $theme_dir;
    //echo '<pre>';print_r($setting);exit();
    foreach($setting['pic'] as $key=>$pic)
    {
        $setting['pic'][$key]['linktarget'] = url::to($pic['linktarget']);
    }
    return $setting;
}
?>

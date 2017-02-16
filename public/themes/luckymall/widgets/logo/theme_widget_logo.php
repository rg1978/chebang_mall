<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_logo($setting){
    $logo_id = app::get('site')->getConf('site.logo');
    if($logo_id)
    {
        $result['logo_image'] = $logo_id;
    }
    else
    {
        $result['logo_image'] = "http://images.bbc.shopex123.com/images/33/e2/ff/56e438276be7f2d7ae2b7bede423048f6847e906.png";
    }
    return $result;

}
?>

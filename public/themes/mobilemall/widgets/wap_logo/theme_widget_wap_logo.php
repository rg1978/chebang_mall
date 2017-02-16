<?php

function theme_widget_wap_logo($setting)
{
    $logo_id = app::get('sysconf')->getConf('sysconf_setting.wap_logo');
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

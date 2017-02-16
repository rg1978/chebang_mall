<?php
function theme_widget_custom_footerlinks(&$setting) {

    $cur_url = $_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
    $setting['max_leng'] = $setting['max_leng'] ? $setting['max_leng'] : 7;
    $setting['showinfo'] = $setting['showinfo'] ? $setting['showinfo'] : app::get('b2c')->_("更多");

    foreach($setting['urls'] as $key=>$val)
    {
        $ret[$key]['title'] = $val['title'];
        $ret[$key]['url'] = url::to($val['link']);
        if (request::url() == $ret[$key]['url'])
              $ret[$key]['hilight'] = true;
    }
    return $ret;//$result;
    return $setting;
}

?>

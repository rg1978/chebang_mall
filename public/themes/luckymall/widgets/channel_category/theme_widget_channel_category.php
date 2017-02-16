<?php

function theme_widget_channel_category(&$setting,&$env){
    // 判断是否首页
    $returnData = $setting;
    if (route::currentRouteName() == 'topc')
    {
        $returnData['isindex'] = true;
    }

    $cat_list = app::get('topc')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name'));

    $returnData['topics_catlist'] = $cat_list[$setting['topics_cat_id']];

    return $returnData;
}

?>
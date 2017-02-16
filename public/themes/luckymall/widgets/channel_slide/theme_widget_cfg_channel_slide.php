<?php
function theme_widget_cfg_channel_slide($app, &$setting){

    $returnData['cats'] = app::get('topc')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name'));
    
    foreach($returnData['cats'][$setting['topics_cat_id']]['lv2'] as &$vlv2)
    {
        foreach ($vlv2['lv3'] as &$vlv3) {
            if( in_array($vlv3['cat_id'], $setting['rec_cat_id'][$vlv2['cat_id']]) )
            {
                $vlv3['checked'] = 1;
            }else{
                $vlv3['checked'] = 0;
            }
        }
    }

    return $returnData;
}
?>
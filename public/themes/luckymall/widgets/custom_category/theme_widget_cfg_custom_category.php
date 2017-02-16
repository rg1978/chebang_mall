<?php
function theme_widget_cfg_custom_category(){

    $returnData['cats'] = app::get('topc')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name'));
    return $returnData;
}
?>
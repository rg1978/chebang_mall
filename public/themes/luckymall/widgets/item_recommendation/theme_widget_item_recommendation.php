<?php
function theme_widget_item_recommendation(&$setting) {
    $rows = 'item_id,title,price,image_default_id';
    $objItem = kernel::single('sysitem_item_info');
    $setting['item'] = $objItem->getItemList($setting['item_select'], $rows);
    $setting['item'] = $setting['item'][$setting['item_select']];
    $setting['defaultImg'] = app::get('image')->getConf('image.set');
    return $setting;
}

?>

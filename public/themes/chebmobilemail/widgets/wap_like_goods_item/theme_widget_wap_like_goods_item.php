<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_wap_like_goods_item(&$setting){

    $rows = 'item_id,shop_id,title,price,image_default_id,cat_id';
    $objItem = kernel::single('sysitem_item_info');
    $setting['item'] = $objItem->getItemList($setting['item_select'], $rows, [], ['limit'=>$setting['limit']]);

    // 获取商品销量
    $objMdlItemCount = app::get('sysitem')->model('item_count');
    $itemInfoCount = $objMdlItemCount->getList('sold_quantity,item_id', array('item_id'=>array_column($setting['item'], 'item_id')));
    foreach ($itemInfoCount as $val)
    {
        if(array_key_exists($val['item_id'], $setting['item']))
        {
            $setting['item'][$val['item_id']]['sold_quantity'] = $val['sold_quantity'];
        }    
    }

    //获取商品所属商铺
    foreach ($setting['item'] as $vo) {
        $shopData = app::get('sysshop')->model('shop')->getRow('shop_type,shop_name',['shop_id'=>$vo['shop_id']]);
        $setting['item'][$vo['item_id']]['shop_name'] = $shopData['shop_name'];
    }

    return $setting;
}
?>

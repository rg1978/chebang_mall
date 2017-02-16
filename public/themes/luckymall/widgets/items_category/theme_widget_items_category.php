<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_items_category(&$setting)
{

    $returnData['data'] = app::get('topc')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name,cat_logo'));
    return $returnData;
}

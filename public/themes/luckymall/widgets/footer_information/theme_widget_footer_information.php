<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_footer_information(&$setting,&$smarty)
{
    $articleData = $setting['articlelist'];
    foreach( $articleData  as $row )
    {
        $data = json_decode($row,true);
        $articleIds[] = $data['article_id'];
        $indexs[] = $data;
    }

    $setting['selectmaps'] = $selectmaps;
    $setting['order'] or $setting['order'] = 'desc';
    $setting['order_type'] or $setting['order_type'] = '最后更新时间';

    $tmp['indexs'] = $indexs;
    $tmp['__stripparenturl'] = $setting['stripparenturl'];

    return $tmp;
}
?>

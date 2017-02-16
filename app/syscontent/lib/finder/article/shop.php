<?php

/**
 * shop.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syscontent_finder_article_shop {
    public $column_look = "操作";
    public $column_look_width = 50;
    public function column_look(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            if($row['platform']=='2')
            {
                $url = url::action('topwap_ctl_content@shopArticle', array('aid'=>$row['article_id'], 'shop_id'=>$row['shop_id'], 'preview'=>1));
            }
            else
            {
                $url = url::action('topc_ctl_shopcenter@shopArticle', array('aid'=>$row['article_id'], 'shop_id'=>$row['shop_id'], 'preview'=>1));
            }
    
            $target = '_blank';
            $title = app::get('syscontent')->_('查看');
            $button = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            $colList[$k] = $button;
        }
    }
}
 
<?php

/**
 * save.php 
 * -- syscontent.shop.save.article
 * -- 保存商家文章
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syscontent_api_shop_save {

    public $apiDescription = "保存商家文章";
    
    public function getParams()
    {
        $return['params'] = array();
        return $return;
    }
    
    public function save($params)
    {
        return kernel::single('syscontent_article_shop_article')->save($params);
    }
}
 
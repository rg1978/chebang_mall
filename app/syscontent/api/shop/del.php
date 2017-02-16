<?php
/**
 * del.php 
 * -- syscontent.shop.del.article
 * -- 删除指定商家文章
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class syscontent_api_shop_del{
     public $apiDescription = "删除指定商家文章";
     public function getParams()
     {
         $return['params'] = [
                 'shop_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'商家id','desc'=>'商家id',],
                 'article_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'文章id','desc'=>'文章id',],
         ];
         return $return;
     }
     
     public function del($params)
     {
         $filter['shop_id'] = intval($params['shop_id']);
         $filter['article_id'] = intval($params['article_id']);
         
         $objMdl = app::get('syscontent')->model('article_shop');
         
         return $objMdl->delete($filter);
     }
 }
 
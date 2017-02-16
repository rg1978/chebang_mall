<?php
/**
 * info.php 
 * -- syscontent.shop.info.article
 * -- 获取指定商家文章详情
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class syscontent_api_shop_info{
     
     public $apiDescription = "获取指定商家文章详情";
     public function getParams()
     {
         $return['params'] = [
                 'shop_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'商家id','desc'=>'商家id',],
                 'article_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'文章id','desc'=>'文章id',],
                 'fields'=> ['type'=>'field_list','valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'需要的字段','desc'=>'需要的字段',],
         ];
         return $return;
     }
     /**
      * 获取商家文章列表
      * @desc 用于获取商家文章列表
      * @return int article_id 文档ID
      * @return string title 文章标题
      * @return int modified 文章最后修改时间
      * @return string content 文章内容
      * @return int node_id 文章所属类目ID
      * @return int shop_id 商家店铺ID
      */
     public function get($params)
     {
         $filter['shop_id'] = intval($params['shop_id']);
         $filter['article_id'] = intval($params['article_id']);
         $objMdl = app::get('syscontent')->model('article_shop');
         
         return $objMdl->getRow($params['fields'], $filter);
     }
 }
 
<?php
/**
 * delNode.php 
 * -- syscontent.shop.del.article.node
 * -- 删除指定商家文章分类
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class syscontent_api_shop_delNode{
     
     public $apiDescription = '删除指定商家文章分类';
     /**
      * 定义API传入的应用级参数
      * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
      * @return array 返回传入参数
      */
     public function getParams()
     {
         //接口传入的参数
         $return['params'] = array(
                 'node_id' => ['type'=>'int',        'valid'=>'required', 'title'=>'分类id',    'example'=>'', 'desc'=>'分类id'],
                 'shop_id' => ['type'=>'int',        'valid'=>'required', 'title'=>'商铺id',    'example'=>'', 'desc'=>'商铺id'],
         );
     
         return $return;
     }
     
     /**
      * 删除指定商家文章分类
      * @desc 用于删除指定商家文章分类
      * @return boolean status succes:true,faild:false
      */
     
     public function del($params)
     {
         $filter['shop_id'] = intval($params['shop_id']);
         $filter['node_id'] = intval($params['node_id']);
         
         $objMdl = app::get('syscontent')->model('article_shop_nodes');
         
         return $objMdl->delete($filter);
     }
 }
 
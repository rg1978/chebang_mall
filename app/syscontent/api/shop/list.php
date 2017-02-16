<?php
/**
 * list.php 
 * -- syscontent.shop.list.article
 * -- 获取商家文章列表
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class syscontent_api_shop_list{
     
     public $apiDescription = "获取商家文章列表";
     public function getParams()
     {
         $return['params'] = [
                'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'分页当前页数','desc'=>'分页当前页数,默认为1',],
                'page_size' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'每页数据条数','desc'=>'每页数据条数,默认20条',],
                'fields'=> ['type'=>'field_list','valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'需要的字段','desc'=>'需要的字段',],
                'node_id' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'节点id','desc'=>'节点id',],
                'orderBy' => ['type'=>'string','valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'排序','desc'=>'排序',],
                'platform' => ['type'=>'string','valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'发布平台','desc'=>'发布平台,多个平台使用英文逗号隔开',],
                'shop_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'商家id','desc'=>'商家id',],
                'keyword' => ['type'=>'string','valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'文章搜索关键字','desc'=>'文章搜索标题关键字',],
                'is_pub' => ['type'=>'bool','valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'是否发布','desc'=>'文章是否发布',],
         ];
         return $return;
     }
     
     /**
      * 获取商家文章列表
      * @desc 用于获取商家文章列表
      * @return int count 文章个数
      * @return int total 文章总页数
      * @return array list 文章列表
      */
     public function getList($params)
     {
         $row = $params['fields'];
         $filter['shop_id'] = intval($params['shop_id']);
         if($params['node_id'])
         {
             $filter['node_id'] = intval($params['node_id']);
         }
         
         if($params['platform'])
         {
             $filter['platform'] = explode(',', $params['platform']);
         }
         
         if($params['keyword'])
         {
             $filter['title|has'] = $params['keyword'];
         }
         
         if($params['is_pub'])
         {
             $filter['pubtime|lthan'] = time();
         }
         
         $orderBy = null;
         if($params['orderBy'])
         {
             $orderBy = $params['orderBy'];
         }
         
         $pageNo = $params['page_no'];
         $pageSize = $params['page_size'];
         
         return kernel::single('syscontent_article_shop_article')->getArticleList($row, $filter, $pageNo, $pageSize, $orderBy);
     }
 }
 
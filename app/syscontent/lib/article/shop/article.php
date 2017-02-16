<?php
/**
 * article.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 class syscontent_article_shop_article{
     private $mdl;
     public function __construct()
     {
         $this->mdl = app::get('syscontent')->model('article_shop');
     }
     public function save($data)
     {
         $this->__checkData($data);
         return $this->mdl->save($data);
     }
     
     // 获取文章列表
     public function getArticleList($row, $filter, $pageNo = null, $pageSize = null, $orderBy=null)
     {
         
         
         $result = ['count'=>0, 'total'=>0, 'list'=>[]];
         $count = $this->mdl->count($filter);
         if(!$count)
         {
             return $result;
         }
         // 取所有的
         if(!$pageNo && !$pageSize)
         {
             $result['count'] = $count;
             $result['list'] = $this->mdl->getList($row, $filter);
             return $result;
         }
         $total = ceil($count/$pageSize);
         if ($pageNo > $total) {
             $pageNo = $total;
         }
         $max = 1000000;
         if($pageSize >= 1 && $pageSize < 500 && $pageNo >=1 && $pageNo < 200 && $pageSize*$pageNo < $max)
         {
             $limit = $pageSize;
             $page = ($pageNo-1)*$limit;
         }
         
         $list = $this->mdl->getList($row, $filter, $page, $limit, $orderBy);
         // 获取文章所属分类
         if(array_key_exists('node_id', $list[0]))
         {
             $nodeIds = array_column($list, 'node_id');
             $objNodeMdl = app::get('syscontent')->model('article_shop_nodes');
             $nodeList = $objNodeMdl->getList('node_name,node_id', ['node_id'=>$nodeIds]);
             foreach ($list as &$row)
             {
                 $row['node_name'] = app::get('syscontent')->_('未分类');
                 foreach ($nodeList as $val)
                 {
                     if($row['node_id'] == $val['node_id'])
                     {
                         $row['node_name'] = $val['node_name'];
                     }
                 }
             }
         }
         
         $result['count'] = $count;
         $result['total'] = $total;
         $result['list'] = $list;
         
         return $result;
     }
     
     public function __checkData($data)
     {
         $validator = validator::make(
                 ['title' => $data['title'], 'content'=>$data['content'], 'node_id'=>$data['node_id']],
                 ['title' => 'required|min:2|max:50', 'content' => 'required|min:8', 'node_id'=>'required'],
                 ['title' => '文章标题不能为空|文章标题至少两个字|文章标题最多五十字', 'content' => '文章内容不能为空|文章内容至少八个字', 'node_id'=>'请选择文章分类']
         );
         $validator->newFails();
         // 判断发布时间误差为一分钟,编辑文章不需要判断
         if(!$data['article_id'])
         {
             if(($data['pubtime']+60)<time())
             {
                 throw new LogicException(app::get('syscontent')->_('发布时间不能小于当前时间'));
             }
         }
         
         if(!$data['shop_id'])
         {
             throw new LogicException(app::get('syscontent')->_('请选择商家'));
         }
     
         return true;
     }
 }
 
<?php

/**
 * article.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_shop_article extends topshop_controller {

    public $shopId;
    public function __construct($app)
    {
        parent::__construct($app);
        $this->shopId = shopAuth::getShopId();
        $this->limit = 20;
    }
    // 文章管理
    public function index()
    {
        $pages = input::get('pages', 1);
        $input = input::get();
        $this->contentHeaderTitle = app::get('topshop')->_('文章管理');
        
        // 组合API请求参数
        if($input['s_k'])
        {
            $params['keyword'] = specialutils::removeXSS($input['s_k']);
        }
        if($input['s_n'])
        {
            $params['node_id'] = $input['s_n'];
        }
        $params['shop_id'] = $this->shopId;
        $params['fields'] = 'article_id,title,platform,node_id,pubtime,modified';
        $params['page_size'] = $this->limit;
        $params['page_no'] = $pages;
        
        $pagedata = app::get('topshop')->rpcCall('syscontent.shop.list.article', $params);
        
        // 分页
        $pagersFilter = $input;
        $pagersFilter['pages'] = time();
        $pagers = array(
                'link'=>url::action('topshop_ctl_shop_article@index',$pagersFilter),
                'current'=>$pages,
                'use_app' => 'topshop',
                'total'=>$pagedata['total'],
                'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;
        
        // 获取文章分类
        $pagedata['nodes'] = $this->__getNodeList('node_id,node_name', 'node_id desc');
        $pagedata['search']['keyword'] = $input['s_k'];
        $pagedata['search']['node'] = $input['s_n'];
        $pagedata['shop_id'] = $this->shopId;
        
        return $this->page('topshop/shop/article/index.html', $pagedata);
    }
    
    // 删除文章
    public function delArticle()
    {
        $params['shop_id'] = $this->shopId;
        $params['article_id'] = input::get('id');
        try {
            $result = app::get('topshop')->rpcCall('syscontent.shop.del.article', $params);
            if(!$result)
            {
                throw new LogicException(app::get('topshop')->_('删除失败'));
            }
        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage());
        }
        $this->sellerlog( app::get('topshop')->_('删除文章，文章ID：'.$params['article_id']));
        return $this->splash('success', url::action('topshop_ctl_shop_article@index'), app::get('topshop')->_('删除成功'));
    }
    
    // 编辑文章
    public function editArticle()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('编辑文章');
        $articleId = input::get('id', 0);
        // 获取分类
        $nodeList = $this->__getNodeList('node_id,node_name', 'order_sort asc,node_id desc');
        // 如果还没有分类就跳转到分类设置
        if(!$nodeList['count'])
        {
            redirect::action('topshop_ctl_shop_article@nodes')->send();exit;
        }
        
        $pagedata['nodes'] = $nodeList['list'];
        $pagedata['article']['pubtime'] = time();
        if($articleId)
        {
            $params['shop_id'] = $this->shopId;
            $params['article_id'] = $articleId;
            $params['fields'] = '*';
            $info = app::get('topshop')->rpcCall('syscontent.shop.info.article', $params);
            $pagedata['article'] = $info;
        }
        
        return $this->page('topshop/shop/article/edit.html', $pagedata);
    }
    
    // 保存文章
    public function saveArticle()
    {
        $post = input::get('article');
        
        try {
            $params = $this->__checkArticle($post);
            $result = app::get('topshop')->rpcCall('syscontent.shop.save.article', $params);
            
            if(!$result)
            {
                throw new LogicException(app::get('topshop')->_('保存失败'));
            }
            
        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage());
        }
        
        $this->sellerlog( app::get('topshop')->_('保存文章，文章标题：'.$params['title']));
        $url = url::action('topshop_ctl_shop_article@index');
        return $this->splash('success', $url, app::get('topshop')->_('保存成功'));
    }
    
    private function __checkArticle($data)
    {
        $validator = validator::make(
                ['title' => $data['title'], 'content'=>$data['content']],
                ['title' => 'required|min:2|max:50', 'content' => 'required|min:8'],
                ['title' => '文章标题不能为空|文章标题至少两个字|文章标题最多五十字', 'content' => '文章内容不能为空|文章内容至少八个字']
        );
        $validator->newFails();
        // 判断发布时间误差为一分钟,编辑文章不需要判断
        if(!$data['id'])
        {
            $pubtime = strtotime($data['pubtime'])+60;
            if($pubtime<time())
            {
                throw new LogicException(app::get('topshop')->_('发布时间不能小于当前时间'));
            }
        }
        
        if(!$data['node'])
        {
            throw new LogicException(app::get('topshop')->_('请选择文章分类'));
        }
        
        $params['shop_id'] = $this->shopId;
        $params['title'] = htmlspecialchars($data['title']);
        $params['content'] = $data['content'];
        $params['article_id'] = $data['id'];
        $params['platform'] = $data['use_platform'];
        $params['node_id'] = $data['node'];
        $params['pubtime'] = strtotime($data['pubtime']);
        $params['modified'] = time();
        
        return $params;
    }
    // 文章分类
    public function nodes()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('文章分类');
        $result = $this->__getNodeList('node_id,node_name,order_sort,modified', 'order_sort asc,node_id desc');
        $pagedata['nodes'] = $result;
        
        return $this->page('topshop/shop/article/nodes.html', $pagedata);
    }
    
    // 编辑文章分类
    public function editNode()
    {
        $nodeId = input::get('node_id', 0);
        
        try {
            $result = [];
            if($nodeId)
            {
                $params['node_id'] = $nodeId;
                $params['shop_id'] = $this->shopId;
                $params['fields'] = 'order_sort,node_name,node_id';
                $result = app::get('topshop')->rpcCall('syscontent.shop.get.article.node', $params);
            }
            $data['success'] = true;
            $data['html'] = view::make('topshop/shop/article/nodes_modal.html', $result)->render();
        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage());
        }
        
        return response::json($data);exit;
    }
    
    // 保存分类
    public function saveNode()
    {
        $postdata = specialutils::filterInput(input::get());
        $postdata['shop_id'] = $this->shopId;
        try {
            $this->__checkNodeData($postdata);
            $result = app::get('topshop')->rpcCall('syscontent.shop.save.article.node', $postdata);
            if(!$result)
            {
                return $this->splash('error',null, app::get('topshop')->_('保存失敗'));
            }
        } catch (Exception $e) {
            return $this->splash('error',null, $e->getMessage());
        }
        $this->sellerlog( app::get('topshop')->_('保存文章分类，分类名称：'.$postdata['node_name']));
        $url = url::action('topshop_ctl_shop_article@nodes');
        return $this->splash('success', $url, app::get('topshop')->_('保存成功'));
    }
    
    // 删除分类
    public function delNode()
    {
        $nodeId = input::get('node_id', 0);
        try {
            $params['node_id'] = $nodeId;
            $params['shop_id'] = $this->shopId;
            $result = app::get('topshop')->rpcCall('syscontent.shop.del.article.node', $params);
            if(!$result)
            {
                return $this->splash('error',null, app::get('topshop')->_('刪除失敗'));
            }
        } catch (Exception $e) {
            return $this->splash('error',null, $e->getMessage());
        }
        $this->sellerlog( app::get('topshop')->_('删除文章分类，分类id：'.$nodeId));
        $url = url::action('topshop_ctl_shop_article@nodes');
        return $this->splash('success', $url, app::get('topshop')->_('刪除成功'));
    }
    
    // 获取分类列表
    private function __getNodeList($fields, $orderBy)
    {
        $params['shop_id'] = $this->shopId;
        $params['fields'] = $fields;
        $params['order_by'] = $orderBy;
        $result = app::get('topshop')->rpcCall('syscontent.shop.list.article.node', $params);
        return $result;
    }
    
    private function __checkNodeData($data)
    {
        $validator = validator::make(
                ['title' => $data['node_name']],
                ['title' => 'required|min:2|max:20'],
                ['title' => '分类名称不能为空|分类名称至少两个字|分类名称最多二十个字']
        );
        $validator->newFails();
        
        if($data['order_sort'])
        {
            if(intval($data['order_sort']) != $data['order_sort'] || $data['order_sort'] < 0)
            {
                throw new LogicException(app::get('topshop')->_('分类排序为正整数'));
            }
        }
        
        return true;
    }
}
 
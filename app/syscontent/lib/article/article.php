<?php


/*
 * @package content
 * @subpackage article
 * @copyright Copyright (c) 2015, shopex. inc
 * @author gongjiapeng@shopex.cn
 * @license
 */
class syscontent_article_article
{
    //保存文章
    public function saveContent($data)
    {
        $nodeMdl = app::get('syscontent')->model('article_nodes');
        $articleMdl = app::get('syscontent')->model('article');
        $contentData = $this->__checkdata($data);
        $db = app::get('syscontent')->database();
        $db->beginTransaction();
        try
        {
            $articleMdl->save($contentData['article']);
            $nodeMdl->save($contentData['node']);
            $db->commit();
        }
        catch(LogicException $e)
        {
            $db->rollback();
            throw $e;
        }
    }
    private function __checkdata($contentData)
    {
        $nodeMdl = app::get('syscontent')->model('article_nodes');
        $validator = validator::make(
            ['title' => $contentData['title'] , 'node_id' => $contentData['node_id']],
            ['title' => 'required'            , 'node_id' => 'required'],
            ['title' => '文章标题不能为空'      , 'node_id' => '文章节点id不能为空']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new LogicException( $error[0] );
            }
        }

        $nodeInfo = $nodeMdl->getRow('parent_id',array('node_id'=>$contentData['node_id']));
        if($nodeInfo['parent_id']==0)
        {
            throw new \LogicException('请选择二级分类!');
        }
        $data['node']['node_id'] = $contentData['node_id'];
        $data['article']['content'] = $contentData['content'];
        $data['article']['node_id'] = $contentData['node_id'];
        $data['article']['title'] = $contentData['title'];
        $data['article']['platform'] = $contentData['platform'];
        $data['article']['article_logo'] = $contentData['article_logo'];
        $data['article']['modified'] = time();
        $data['article']['platform'] = $contentData['platform'];
        $data['article']['type'] = $contentData['type'];
        $data['article']['tmpl_path'] = $contentData['tmpl_path'];
        if($contentData['article_id'])
        {
            $data['article']['article_id'] = $contentData['article_id'];
        }
        return $data;
    }

    //根据文章id获取信息
    public function getArticleInfo($params)
    {
        $articleMdl = app::get('syscontent')->model('article');
        if($params['article_id']=='')
        {
            throw new \LogicException('文章id不能为空!');
        }
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $articleInfo = $articleMdl->getRow($params['fields'],array('article_id'=>$params['article_id']));
        return $articleInfo;
    }

    public function getArticleList($params)
    {
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        if(!$params['platform'])
        {
            $params['platform'] = 'pc';
        }
      /*  if($params['node_id']=='')
        {
            throw new \LogicException('节点id不能为空!');
        }*/
        $pageNo = $params['page_no'] ? $params['page_no']:'1';
        $pageSize = $params['page_size'] ? $params['page_size']:'20';
        $articleMdl = app::get('syscontent')->model('article');
        $nodeMdl = app::get('syscontent')->model('article_nodes');
        $filter = array('node_id'=>$params['node_id'],'platform'=>$params['platform']);
        $nodeInfo = $nodeMdl->getRow('node_id,node_name',$filter);
        //分页处理
        $articleCount = $articleMdl->count($filter);
        $total = ceil($articleCount/$pageSize);
        if ($pageNo > $total) {
            $pageNo = $total;
        }
        $max = 1000000;
        if($pageSize >= 1 && $pageSize < 500 && $pageNo >=1 && $pageNo < 200 && $pageSize*$pageNo < $max)
        {
            $limit = $pageSize;
            $page = ($pageNo-1)*$limit;
        }
        $orderBy    = $params['orderBy'] ? $params['orderBy'] : 'modified DESC';

        $aData = $articleMdl->getList($params['fields'], $filter,$page,$limit, $orderBy);

        $articleData = array(
                'articleList'  => $aData,
                'articlecount' => $articleCount,
                'nodeInfo'     =>$nodeInfo,
            );

        return $articleData;
    }
}

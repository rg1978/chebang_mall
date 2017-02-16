<?php

/**
 * listNode.php 
 * -- syscontent.shop.list.article.node
 * -- 获取商家文章分类
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syscontent_api_shop_listNode {
    
    public $apiDescription = '获取商家文章分类';
    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
                'fields'     => ['type'=>'field_list', 'valid'=>'required',         'title'=>'需要的字段', 'example'=>'', 'desc'=>'需要的字段'],
                'shop_id' => ['type'=>'int',        'valid'=>'required', 'title'=>'商家id',    'example'=>'', 'desc'=>'商家id'],
                'node_ids'=>['type'=>'string',        'valid'=>'', 'title'=>'分类ID',    'example'=>'2,1,3', 'desc'=>'分类ID集合,多个id使用英文逗号隔开'],
                'order_by'=>['type'=>'string',        'valid'=>'', 'title'=>'排序条件',    'example'=>'order_sort desc', 'desc'=>'排序条件'],
        );
    
        return $return;
    }
    
    /**
     * 获取商家文章分类
     * @desc 用于获取商家文章分类
     * @return int count 分类个数
     * @return array list 分类列表
     */
    public function getList($params)
    {
        $filter['shop_id'] = intval($params['shop_id']);
        if($params['node_ids'])
        {
            $filter['node_id'] = explode(',', $params['node_ids']);
        }
        $objMdl = app::get('syscontent')->model('article_shop_nodes');
        $result = ['count'=>0, 'list'=>[]];
        $count = $objMdl->count($filter);
        if(!$count)
        {
            return $result;
        }
        $orderBy = null;
        if($params['order_by'])
        {
            $orderBy = $params['order_by'];
        }
        $list = $objMdl->getList($params['fields'], $filter, 0, -1, $orderBy);
        
        $result['count'] = $count;
        $result['list'] = $list;
        
        return $result;
    }
}
 
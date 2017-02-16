<?php

/**
 * getNode.php 
 * -- syscontent.shop.get.article.node
 * -- 获取指定商家分类详情
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syscontent_api_shop_getNode {

    public $apiDescription = '获取指定商家分类详情';
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
                'shop_id' => ['type'=>'int', 'valid'=>'required', 'title'=>'商家id',    'example'=>'', 'desc'=>'商家id'],
                'node_id'=>['type'=>'int', 'valid'=>'required', 'title'=>'分类ID',    'example'=>'', 'desc'=>'分类ID'],
        );
    
        return $return;
    }
    
    /**
     * 获取指定商家分类详情
     * @desc 用于获取指定商家分类详情
     * @return int node_id 分类id
     * @return string node_name 分类名字
     * @return int order_sort 分类排序
     */
    public function get($params)
    {
        $filter['shop_id'] = intval($params['shop_id']);
        $filter['node_id'] = intval($params['node_id']);
        
        $objMdl = app::get('syscontent')->model('article_shop_nodes');
        $result = $objMdl->getRow($params['fields'], $filter);
        
        return $result;
    }

}
 
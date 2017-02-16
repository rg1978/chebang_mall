<?php
class syscontent_api_getContentMaps {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取文章节点的map';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        return [];
    }

    public function get($params)
    {
        $syscontentLibNode = kernel::single('syscontent_article_node');
        try
        {
            $maps = $syscontentLibNode->getNodeList();
        }
        catch(Exception $e)
        {
            throw new \LogicException($e->getMessage());
        }
        return $maps;
    }

}

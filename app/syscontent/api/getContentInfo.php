<?php
/**
 * ShopEx licence
 * - syscontent.content.get.info
 * - 用于获取文章的详情
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class syscontent_api_getContentInfo {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取文章详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'fields'     => ['type'=>'field_list', 'valid'=>'',         'title'=>'需要的字段', 'example'=>'', 'desc'=>'需要的字段'],
            'article_id' => ['type'=>'int',        'valid'=>'required', 'title'=>'文章id',    'example'=>'', 'desc'=>'文章id'],
        );

        return $return;
    }

    /**
     * 获取单个商品的详细信息
     * @desc 用于获取单个商品的详细信息
     * @return int article_id 文档ID
     * @return string title 文章标题
     * @return int modified 文章最后修改时间
     * @return string content 文章内容
     * @return int node_id 文章所属类目ID
     */
    public function getContentInfo($params)
    {
        $syscontentLibArticle = kernel::single('syscontent_article_article');
        try
        {
            $syscontentInfo = $syscontentLibArticle->getArticleInfo($params);

        }
        catch(Exception $e)
        {
            throw new \LogicException($e->getMessage());
        }
        return $syscontentInfo;
    }

}

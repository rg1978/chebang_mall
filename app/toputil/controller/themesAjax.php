<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_ctl_themesAjax {

    public function getContentNodeArticleList()
    {
        $nodeId = input::get('node_id');
        if( $nodeId )
        {
            $params['node_id'] = $nodeId;
        }

        $nodeData = app::get('topc')->rpcCall('syscontent.node.get.list', array('parent_id'=>$nodeId,'fields'=>'node_id'));
        if($nodeData)
        {
            foreach ($nodeData as $key => $value)
            {
                $nodeIds[$key] = $value['node_id'];
            }
        }
        else
        {
            $nodeIds = $nodeId;
        }

        $params = ['node_id'=>$nodeIds,'fields'=>'article_id,title,node_id,modified','platform'=>'pc'];
        $data = app::get('topc')->rpcCall('syscontent.content.get.list',$params);

        return response::json($data['articleList']);
    }

    /**
     * 根据父类id获取子类列表
     * @return json
     */
    public function getChildrenCatList()
    {
        $catId = intval(input::get('cat_id'));
        if($catId)
        {
            $catList = app::get('toputil')->rpcCall('category.cat.get.info',array('parent_id'=>$catId,'fields'=>'cat_id,cat_name,child_count'));
            foreach($catList as $key=>$value) {
                $newList[$key] = array(
                    'value' => $value['cat_id'],
                    'text' => $value['cat_name'],
                    'hasChild' => ($value['child_count'] >0) ? true : false,
                );
            }
            $data['data']['options'] = $newList;
        }
        else
        {
            $data=array();
        }
        return response::json($data);
    }
}

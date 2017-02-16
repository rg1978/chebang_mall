<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取多条满减促销列表
 */
final class syspromotion_api_fulldiscount_fulldiscountItemList{

    public $apiDescription = '获取多条满折促销商品列表';

    public function getParams()
    {
        $return['params'] = array(
            'fulldiscount_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'满折促销id'],
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
        );

        return $return;
    }

    /**
     * 获取满减促销列表
     */
    public function fulldiscountItemList($params)
    {
        $objMdlFulldiscountItem = app::get('syspromotion')->model('fulldiscount_item');
        $objMdlFulldiscount = app::get('syspromotion')->model('fulldiscount');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $fulldiscountInfo = $objMdlFulldiscount->getRow('*',array('fulldiscount_id'=>$params['fulldiscount_id']));
        if($fulldiscountInfo['fulldiscount_status']=='agree')
        {
            $filter = array('fulldiscount_id'=>$params['fulldiscount_id']);
            $countTotal = $objMdlFulldiscountItem->count($filter);
            if( $countTotal )
            {
                $pageTotal = ceil($countTotal/$params['page_size']);
                $page =  $params['page_no'] ? $params['page_no'] : 1;
                $limit = $params['page_size'] ? $params['page_size'] : 10;
                $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
                $offset = ($currentPage-1) * $limit;
                $orderBy = $params['orderBy'] ? $params['orderBy'] : 'fulldiscount_id DESC';
                $fulldiscountItemList = $objMdlFulldiscountItem->getList($params['fields'], $filter, $offset, $limit, $orderBy);
                $fulldiscountItem = array(
                    'list'=>$fulldiscountItemList,
                    'total_found'=>$countTotal,
                );
            }
            if(!$fulldiscountInfo['fulldiscount_desc'])
            {
                $objLibProdesc = kernel::single('syspromotion_promotiondesc');
                $fulldiscountInfo['fulldiscount_desc'] = $objLibProdesc->promotionRule($fulldiscountInfo, 'fulldiscount');
            }
            $fulldiscountItem['promotionInfo'] = $fulldiscountInfo;
            return $fulldiscountItem;
        }
        else
        {
            return false;
        }
    }

}


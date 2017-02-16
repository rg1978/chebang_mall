<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取多条满减促销列表
 */
final class syspromotion_api_fullminus_fullminusItemList{

    public $apiDescription = '获取多条满减促销商品列表';

    public function getParams()
    {
        $return['params'] = array(
            'fullminus_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'满减促销id'],
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
        );

        return $return;
    }

    /**
     * 获取满减促销商品列表
     */
    public function fullminusItemList($params)
    {
        $objMdlFullminusItem = app::get('syspromotion')->model('fullminus_item');
        $objMdlFullminus = app::get('syspromotion')->model('fullminus');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $fullminusInfo = $objMdlFullminus->getRow('*',array('fullminus_id'=>$params['fullminus_id']));
        if($fullminusInfo['fullminus_status']=='agree')
        {
            $filter = array('fullminus_id'=>$params['fullminus_id']);
            $countTotal = $objMdlFullminusItem->count($filter);
            if( $countTotal )
            {
                $pageTotal = ceil($countTotal/$params['page_size']);
                $page =  $params['page_no'] ? $params['page_no'] : 1;
                $limit = $params['page_size'] ? $params['page_size'] : 10;
                $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
                $offset = ($currentPage-1) * $limit;
                $orderBy = $params['orderBy'] ? $params['orderBy'] : 'fullminus_id DESC';
                $fullminusItemList = $objMdlFullminusItem->getList($params['fields'], $filter, $offset, $limit, $orderBy);
                $fullminusItem = array(
                    'list'=>$fullminusItemList,
                    'total_found'=>$countTotal,
                );
            }
            //满减优惠规则
            if(!$fullminusInfo['fullminus_desc'])
            {
                $objLibProdesc = kernel::single('syspromotion_promotiondesc');
                $fullminusInfo['fullminus_desc'] = $objLibProdesc->promotionRule($fullminusInfo, 'fullminus');
            }
            $fullminusItem['promotionInfo'] = $fullminusInfo;
            return $fullminusItem;
        }
        else
        {
            return false;
        }
    }

}


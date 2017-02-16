<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取多条满减促销列表
 */
final class syspromotion_api_freepostage_freepostageItemList{

    public $apiDescription = '获取多条免邮促销商品列表';

    public function getParams()
    {
        $return['params'] = array(
            'freepostage_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'优惠券促销id'],
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'freepostage_id asc'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
        );

        return $return;
    }

    /**
     * 获取满减促销列表
     */
    public function freepostageItemList($params)
    {
        if($params['freepostage_id']=='')
        {
            return false;
        }
        $objMdlFreepostageItem = app::get('syspromotion')->model('freepostage_item');
        $objMdlFreepostage = app::get('syspromotion')->model('freepostage');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $freepostageInfo = $objMdlFreepostage->getRow('*',array('freepostage_id'=>$params['freepostage_id']));
        if($freepostageInfo['freepostage_status']=='agree')
        {
            /*if($freepostageInfo['use_bound']==0)
            {
                $freepostageItem = app::get('syspromotion')->rpcCall('item.search',array('shop_id'=>$freepostageInfo['shop_id'],'fields'=>$params['fields']));

            }
            elseif($freepostageInfo['use_bound']==1)
            {*/
            $count = $objMdlFreepostageItem->count($filter);

            //分页使用
            $pageTotal = ceil($count/$params['page_size']);
            $page =  $params['page_no'] ? $params['page_no'] : 1;
            $limit = $params['page_size'] ? $params['page_size'] : 10;
            $currentPage = $pageTotal < $page ? $pageTotal : $page;
            $offset = ($currentPage-1) * $limit;


            $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' freepostage_id DESC';
            $filter = array('freepostage_id'=>$params['freepostage_id']);
            $freepostageItemList = $objMdlFreepostageItem->getList($params['fields'],$filter,$offset, $limit, $orderBy);
            $freepostageItem = array(
                'list'=>$freepostageItemList,
                'total_found'=>$count,
            );
            //}
            if(!$freepostageInfo['freepostage_desc'])
            {
                $objLibProdesc = kernel::single('syspromotion_promotiondesc');
                $freepostageInfo['freepostage_desc'] = $objLibProdesc->promotionRule($freepostageInfo, 'freepostage');
            }
            $freepostageItem['promotionInfo'] = $freepostageInfo;
        }
        else
        {
            return false;
        }
        //echo '<pre>';print_r($freepostageItem);exit();
        return $freepostageItem;
    }


}


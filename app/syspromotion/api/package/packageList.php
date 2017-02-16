<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取多条组合促销促销列表
 * promotion.package.list
 */
final class syspromotion_api_package_packageList {

    public $apiDescription = '获取多条组合促销促销列表';

    public function getParams()
    {
        $return['params'] = array(
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID'],
            'package_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销id'],
            'package_name' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销名称'],
            'package_status' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销状态'],
            'is_valid' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'可用组合促销'],
            'platform' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销适用平台'],
        );

        return $return;
    }


    /**
     * 获取组合促销促销列表
     */
    public function packageList($params)
    {
        $objMdlPackage = app::get('syspromotion')->model('package');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $filter = array('shop_id'=>$params['shop_id']);

        // 平台未选择则默认全选
        if( $params['platform'] == 'pc' )
        {
            $filter['used_platform'] = array('0', '1');
        }
        elseif( $params['platform'] == 'wap' )
        {
            $filter['used_platform'] = array('0', '2');
        }
        else
        {
            $filter['used_platform'] = array('0','1','2');
        }
        // 获取有效可使用的折扣
        if($params['is_valid'])
        {
            $filter['start_time|lthan'] = time();
            $filter['end_time|than'] = time();
        }

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' package_id DESC';
        $packageTotal = $objMdlPackage->count($filter);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $pageTotal = ceil($packageTotal/$limit);
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;


        $packageData = $objMdlPackage->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $result = array(
            'data' => $packageData,
            'total' => $packageTotal,
        );

        return $result;
    }


}


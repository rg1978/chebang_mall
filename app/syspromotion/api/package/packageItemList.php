<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取多条组合促销促销列表
 * promotion.packageitem.list
 */
final class syspromotion_api_package_packageItemList{

    public $apiDescription = '获取多条组合促销促销商品列表';

    public function getParams()
    {
        $return['params'] = array(
            'package_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销id'],
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
        );

        return $return;
    }

    /**
     * 获取组合促销商品列表
     */
    public function packageItemList($params)
    {
        $objMdlPackageItem = app::get('syspromotion')->model('package_item');
        $objMdlPackage = app::get('syspromotion')->model('package');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $packageInfo = $objMdlPackage->getRow('*',array('package_id'=>$params['package_id']));
        if($packageInfo)
        {
            $filter = array('package_id'=>$params['package_id']);
            $countTotal = $objMdlPackageItem->count($filter);
            if( $countTotal )
            {
                $pageTotal = ceil($countTotal/$params['page_size']);
                $page =  $params['page_no'] ? $params['page_no'] : 1;
                $limit = $params['page_size'] ? $params['page_size'] : 10;
                $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
                $offset = ($currentPage-1) * $limit;
                $orderBy = $params['orderBy'] ? $params['orderBy'] : 'package_id DESC';
                $packageItemList = $objMdlPackageItem->getList($params['fields'], $filter, $offset, $limit, $orderBy);
                $packageItem = array(
                    'list'=>$packageItemList,
                    'total_found'=>$countTotal,
                );
            }
            $packageItem['promotionInfo'] = $packageInfo;
            return $packageItem;
        }
        else
        {
            return false;
        }
    }


}


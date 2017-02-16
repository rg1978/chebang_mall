<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取单条组合促销促销数据
 * promotion.package.getPackageItemsByItemId
 */
final class syspromotion_api_package_getPackageItemsByItemId {

    public $apiDescription = '根据商品id获取商品的组合促销信息及关联商品';

    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'int', 'valid'=>'required|int', 'default'=>'', 'example'=>'', 'description'=>'商品id必填'],
        );

        return $return;
    }

    /**
     *  根据商品id获取商品的组合促销信息及关联商品
     * @param  array $params 筛选条件数组
     * @return array         返回一条组合促销促销信息
     */
    public function getPackageItemsByItemId($params)
    {
        $objMdlPackage = app::get('syspromotion')->model('package');
        $objMdlPackageItem = app::get('syspromotion')->model('package_item');

        // 获取符合条件的组合促销package_id
        $packageItemFilter = array( 'item_id'=>$params['item_id'], 'end_time|than'=>time(), 'start_time|lthan'=>time() );
        $relPackageIds = $objMdlPackageItem->getList('package_id', $packageItemFilter);
        if(!$relPackageIds)return;

        $packageIds = array_column($relPackageIds, 'package_id');

        $packagesInfo = $objMdlPackage->getList('*', array('package_id'=>$packageIds,'package_status'=>'agree')); //组合促销基本信息
        if(!$packagesInfo)return;
        $packagesInfo = array_bind_key($packagesInfo,'package_id');//键值改为package_id

        $relItems = $objMdlPackageItem->getList('*', array('package_id'=>$packageIds)); //组合促销管理商品信息

        $sortRelItems = array();
        foreach($relItems as $v1)
        {
            $k = (intval($v1['item_id']) == $params['item_id']) ? 0 : $v1['item_id'];
            $sortRelItems[$v1['package_id']][$k] = $v1;
        }
        foreach($packagesInfo as &$v2)
        {
            $v2['items'] = $sortRelItems[$v2['package_id']];
        }
        $return['data'] = $packagesInfo;
        return $return;
    }

}


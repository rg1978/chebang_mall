<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取单条组合促销促销数据
 * promotion.package.get
 */
final class syspromotion_api_package_packageGet {

    public $apiDescription = '获取单条组合促销促销数据';

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'会员ID,user_id和shop_id必填一个'],
            'shop_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'店铺ID,user_id和shop_id必填一个'],
            'package_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销id'],
            'package_itemList' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销的商品'],
        );

        return $return;
    }

    /**
     *  获取单条组合促销促销信息
     * @param  array $params 筛选条件数组
     * @return array         返回一条组合促销促销信息
     */
    public function packageGet($params)
    {
        $packageInfo = kernel::single('syspromotion_package')->getPackage($params['package_id']);
        $packageInfo['valid'] = $this->__checkValid($packageInfo);
        if($params['package_itemList'])
        {
            $packageItems = kernel::single('syspromotion_package')->getPackageItems($params['package_id']);
            $packageInfo['itemsList'] = $packageItems;
        }

        return $packageInfo;
    }

    // 检查组合促销是否可用
    private function __checkValid(&$packageInfo)
    {
        $now = time();
        if( ($packageInfo['package_status']=='agree') && ($packageInfo['start_time']<$now) && ($packageInfo['end_time']>$now) )
        {
            return true;
        }
        return false;
    }

}


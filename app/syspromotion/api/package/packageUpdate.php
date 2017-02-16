<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 更新组合促销促销数据
 * promotion.package.update
 */
final class syspromotion_api_package_packageUpdate {

    public $apiDescription = '更新组合促销促销数据';

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'会员ID,user_id和shop_id必填一个'],
            'shop_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'店铺ID,user_id和shop_id必填一个'],
            'package_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销id'],
            'package_name' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销名称'],
            'package_status' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销状态'],
            'package_item_list' => ['type'=>'array', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'package的list[item_id=>[item_id=>id, package_price=>price]]'],
        );


        return $return;
    }

    /**
     *  编辑组合促销促销
     * @param  array $apiData api数据
     * @return bool
     */
    public function packageUpdate($apiData)
    {
        return kernel::single('syspromotion_package')->savePackage($apiData);
    }

}


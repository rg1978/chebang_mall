<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 取消单条组合促销
 */
final class syspromotion_api_package_packageCancel {

    public $apiDescription = '取消单条组合促销';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID必填'],
            'package_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'组合促销ID必填'],
        );

        return $return;
    }

    /**
     * @brief 根据组合促销促销ID取消组合促销促销
     * @author lujy
     * @param $params array
     *
     * @return
     */
    public function packageCancel($params)
    {
        $packageId = $params['package_id'];
        if(!$packageId)
        {
            throw new \LogicException('组合促销id不能为空！');
            return false;
        }
        $objMdlPackage = app::get('syspromotion')->model('package');

        if( !$objMdlPackage->update( array('package_status'=>'cancel'), array('package_id'=>$packageId, 'shop_id'=>$params['shop_id']) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('取消组合促销失败'));
        }

        return true;
    }

}


<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 取消单条满减促销
 */
final class syspromotion_api_fullminus_fullminusCancel {

    public $apiDescription = '取消单条满减促销';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID必填'],
            'fullminus_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'满减促销ID必填'],
        );

        return $return;
    }

    /**
     * @brief 取消满减促销
     * @author lujy
     * @param $params array
     *
     * @return
     */
    public function fullminusCancel($params)
    {
        return kernel::single('syspromotion_fullminus')->fullminusCancel($params);
    }

}


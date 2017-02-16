<?php
class syspromotion_api_gift_giftDelete{
	public $apiDescription = '删除单条赠品促销信息';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID必填'],
            'gift_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品促销ID必填'],
        );

        return $return;
    }

    /**
     * 根据组合促销促销ID删除组合促销促销
     * @param  array $packageId 组合促销促销id
     * @return bool
     */
    public function giftDelete($params)
    {
        return kernel::single('syspromotion_gift')->deleteGift($params);
    }
}
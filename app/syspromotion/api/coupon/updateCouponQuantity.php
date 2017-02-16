<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 更新优惠券数量
 * promotion.coupon.updateCouponQuantity
 */
final class syspromotion_api_coupon_updateCouponQuantity {

    public $apiDescription = '更新优惠券数量';

    public function getParams()
    {
        $return['params'] = array(
            'is_valid' =>['type' =>'string', 'valid'=>'required','default'=>'','example'=>'','description'=>'优惠券状态'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID必填'],
            'coupon_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'优惠券ID必填'],
        );

        return $return;
    }

    /**
     * 更新优惠券数量
     * @param  array $couponId 优惠券id
     * @return bool
     */
    public function updateCouponQuantity($params)
    {
        $db = app::get('syspromotion')->database();
        //判断增加或者减少已使用优惠券数量
        if ($params['is_valid']  == 0) {
            $sqlStr = "UPDATE syspromotion_coupon SET use_couponcode_quantity=use_couponcode_quantity+1 WHERE coupon_id=?";
        }elseif($params['is_valid'] == 1){
            $sqlStr = "UPDATE syspromotion_coupon SET use_couponcode_quantity=use_couponcode_quantity-1 WHERE coupon_id=?";
        }

        if ($db->executeUpdate($sqlStr, [$params['coupon_id']]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}
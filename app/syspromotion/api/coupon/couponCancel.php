<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 取消单条优惠券促销
 */
final class syspromotion_api_coupon_couponCancel {

    public $apiDescription = '取消单条优惠券促销';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID必填'],
            'coupon_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'优惠券促销ID必填'],
        );

        return $return;
    }

    /**
     * @brief 根据优惠券促销ID取消优惠券促销
     * @author lujy
     * @param $params array
     *
     * @return
     */
    public function couponCancel($params)
    {
        $couponId = $params['coupon_id'];
        if(!$couponId)
        {
            throw new \LogicException('优惠券促销id不能为空！');
            return false;
        }
        $objMdlcoupon = app::get('syspromotion')->model('coupon');

        if( !$objMdlcoupon->update( array('coupon_status'=>'cancel'), array('coupon_id'=>$couponId, 'shop_id'=>$params['shop_id']) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('取消优惠券促销失败'));
        }
        app::get('syspromotion')->rpcCall('user.coupon.expire',array('coupon_id'=>$couponId));
        return true;
    }

}


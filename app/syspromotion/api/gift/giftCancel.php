<?php
class syspromotion_api_gift_giftCancel{
	public $apiDescription = '取消单条赠品促销';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID必填'],
            'gift_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品促销ID必填'],
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
    public function giftCancel($params)
    {
        $giftId = $params['gift_id'];
        if(!$giftId)
        {
            throw new \LogicException('赠品促销ID必填');
            return false;
        }
        $objMdlGift = app::get('syspromotion')->model('gift');

        if( !$objMdlGift->update( array('gift_status'=>'cancel'), array('gift_id'=>$giftId, 'shop_id'=>$params['shop_id']) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('取消赠品促销失败'));
        }
        $objMdlGiftItem = app::get('syspromotion')->model('gift_item');
        if( !$objMdlGiftItem->update( array('status'=>0), array('gift_id'=>$giftId, 'shop_id'=>$params['shop_id']) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('取消赠品促销绑定的商品信息失败'));
        }

        $objMdlGiftSku = app::get('syspromotion')->model('gift_sku');
        if( !$objMdlGiftSku->update( array('status'=>0), array('gift_id'=>$giftId, 'shop_id'=>$params['shop_id']) ) )
        {
            throw new \LogicException(app::get('syspromotion')->_('取消赠品促销绑定的商品赠品信息失败'));
        }

        return true;
    }

}

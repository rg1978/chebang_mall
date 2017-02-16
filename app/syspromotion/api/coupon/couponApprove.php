<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * coupon审核接口
 * promotion.coupon.approve
 */
final class syspromotion_api_coupon_couponApprove {

    public $apiDescription = '优惠券审核';

    /**
     *  优惠券审核参数
     * @desc 优惠券审核参数
     * @param  array $apiData 优惠券审核参数
     * @return bool true 
     */
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'', 'title'=>'店铺ID', 'example'=>'', 'desc'=>'店铺ID'],
            'coupon_id' => ['type'=>'int', 'valid'=>'', 'title'=>'优惠券id', 'example'=>'', 'desc'=>'优惠券id'],
            'status' => ['type'=>'string', 'valid'=>'in:agree,refuse,non-reviewed,pending', 'title'=>'审核状态', 'example'=>'', 'desc'=>'审核状态'],
            'reason' => ['type'=>'string', 'valid'=>'required_if:status,refuse', 'title'=>'驳回原因', 'example'=>'', 'desc'=>'驳回原因']
        );

        return $return;
    }

    /**
     *  优惠券审核
     * @desc 用于优惠券审核
     * @return bool true
     */
    public function approve($apiData)
    {
        $objMdlcoupon = app::get('syspromotion')->model('coupon');

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if($apiData['shop_id'] && $apiData['coupon_id']){
                $filter = array('shop_id'=>$apiData['shop_id'],'coupon_id'=>$apiData['coupon_id']);
            }else{
                $filter =array('coupon_status|in' => array('non-reviewed','pending'));
            }
            
            if ($apiData['status'] == 'refuse') { 
                $updateInfo = array(
                    'coupon_status' => $apiData['status'],
        	        'reason' => $apiData['reason'],
    		    ); 
            }else{
                $updateInfo = array('coupon_status' => $apiData['status'],);
            }

            if (!$objMdlcoupon->update($updateInfo,$filter)) {
                throw \LogicException('审核失败！');
            }
            $db->commit();
        }
        catch(Exception $e){
            $db->rollback();
            throw $e;
        }

        return true;
    }
}

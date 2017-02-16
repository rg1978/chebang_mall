<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * gift审核接口
 * promotion.gift.approve
 */
final class syspromotion_api_gift_giftApprove {

    public $apiDescription = '赠品促销审核';

    /**
     *  赠品促销审核参数
     * @desc 用于赠品促销审核参数
     * @param  array $apiData 赠品各种值
     * @return bool true true
     */
    public function getParams()
    {
        $return['params'] = array(
        	'shop_id' => ['type'=>'int', 'valid'=>'', 'title'=>'店铺ID', 'example'=>'', 'desc'=>'店铺ID'],
            'gift_id' => ['type'=>'int', 'valid'=>'', 'title'=>'赠品促销id', 'example'=>'', 'desc'=>'赠品促销id'],
            'status' => ['type'=>'string', 'valid'=>'in:agree,refuse,non-reviewed,pending', 'title'=>'审核状态', 'example'=>'', 'desc'=>'审核状态'],
            'reason' => ['type'=>'string', 'valid'=>'required_if:status,refuse', 'title'=>'驳回原因', 'example'=>'', 'desc'=>'驳回原因']
        );

        return $return;
    }

    /**
     * 赠品促销审核
     * @desc 用于赠品促销审核
     * @return bool true true
     */
    public function approve($apiData)
    {
        $objMdlgift = app::get('syspromotion')->model('gift');
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if($apiData['shop_id'] && $apiData['gift_id']){
                $filter = array('shop_id'=>$apiData['shop_id'],'gift_id'=>$apiData['gift_id']);
            }else{
                $filter =array('gift_status|in' => array('non-reviewed','pending'));
            }
            
        	if ($apiData['status'] == 'refuse') {
		        $updateInfo = array(
		            'gift_status' => $apiData['status'],
			        'reason' => $apiData['reason'],
		        );
        	}else{
                $updateInfo = array('gift_status' => $apiData['status'],);
            } 

        	if (!$objMdlgift->update($updateInfo,$filter)) {
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

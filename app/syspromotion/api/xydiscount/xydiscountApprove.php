<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * xydiscount审核接口
 * promotion.xydiscount.approve
 */
final class syspromotion_api_xydiscount_xydiscountApprove {

    public $apiDescription = 'X件Y折促销审核';

    /**
     *  X件Y折促销审核参数
     * @desc 用于X件Y折促销审核参数
     * @param  array $apiData X件Y折促销各种值
     * @return bool true true
     */
    public function getParams()
    {
        $return['params'] = array(
        	'shop_id' => ['type'=>'int', 'valid'=>'', 'title'=>'店铺ID', 'example'=>'', 'desc'=>'店铺ID'],
            'xydiscount_id' => ['type'=>'int', 'valid'=>'', 'title'=>'X件Y折促销id', 'example'=>'', 'desc'=>'X件Y折促销id'],
            'status' => ['type'=>'string', 'valid'=>'in:agree,refuse,non-reviewed,pending', 'title'=>'审核状态', 'example'=>'', 'desc'=>'审核状态'],
            'reason' => ['type'=>'string', 'valid'=>'required_if:status,refuse', 'title'=>'驳回原因', 'example'=>'', 'desc'=>'驳回原因']
        );

        return $return;
    }

    /**
     *  X件Y折促销审核
     * @desc 用于X件Y折促销审核
     * @return bool true true
     */
    public function approve($apiData)
    {
        $objMdlxydiscount = app::get('syspromotion')->model('xydiscount');
        $objMdlPromotions = app::get('syspromotion')->model('promotions');
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if($apiData['xydiscount_id']){
                $filter2 = array('rel_promotion_id' => $apiData['xydiscount_id'],'promotion_type'=>'xydiscount');
            }else{
                $filter2 = array(
                    'promotion_type'=>'xydiscount',
                    'check_status|in' => array('non-reviewed','pending'));
            }
            
            if($apiData['shop_id'] && $apiData['xydiscount_id']){
                $filter = array('shop_id'=>$apiData['shop_id'],'xydiscount_id'=>$apiData['xydiscount_id']);
            }else{
                $filter =array('xydiscount_status|in' => array('non-reviewed','pending'));
            }
            
        	if ($apiData['status'] == 'refuse') {
		        $updateInfo = array(
		            'xydiscount_status' => $apiData['status'],
			        'reason' => $apiData['reason'],
		        );
        	}else{
                $updateInfo = array('xydiscount_status' => $apiData['status'],);
            }
            $result = $objMdlPromotions->update(array('check_status'=>$apiData['status']),$filter2); 

        	if (!$objMdlxydiscount->update($updateInfo,$filter) && !$result) {
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

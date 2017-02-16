<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * fullminus审核接口
 * promotion.fullminus.approve
 */
final class syspromotion_api_fullminus_fullminusApprove {

    public $apiDescription = '满减审核';

    /**
     *  满减审核参数
     * @desc 满减审核参数
     * @param  array $apiData 满减审核参数
     * @return bool true 
     */
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'', 'title'=>'店铺ID', 'example'=>'', 'desc'=>'店铺ID'],
            'fullminus_id' => ['type'=>'int', 'valid'=>'', 'title'=>'满减规则id', 'example'=>'', 'desc'=>'满减规则id'],
            'status' => ['type'=>'string', 'valid'=>'in:agree,refuse,non-reviewed,pending', 'title'=>'审核状态', 'example'=>'', 'desc'=>'审核状态'],
            'reason' => ['type'=>'string', 'valid'=>'required_if:status,refuse', 'title'=>'驳回原因', 'example'=>'', 'desc'=>'驳回原因']
        );

        return $return;
    }

    /**
     *  满减审核
     * @desc 用于满减审核
     * @return bool true
     */
    public function approve($apiData)
    {
        $objMdlfullminus = app::get('syspromotion')->model('fullminus');
        $objMdlPromotions = app::get('syspromotion')->model('promotions');

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if($apiData['shop_id'] && $apiData['fullminus_id']){
                $filter = array('shop_id'=>$apiData['shop_id'],'fullminus_id'=>$apiData['fullminus_id']);
            }else{
                $filter =array('fullminus_status|in' => array('non-reviewed','pending'));
            }
            
            if($apiData['fullminus_id']){
                $filter2 = array('rel_promotion_id' => $apiData['fullminus_id'],'promotion_type'=>'fullminus');
            }else{
                $filter2 = array(
                    'promotion_type'=>'fullminus',
                    'check_status|in' => array('non-reviewed','pending'));
            }
            
            if ($apiData['status'] == 'refuse') {
    	        $updateInfo = array(
    	           'fullminus_status' => $apiData['status'],
    	           'reason' => $apiData['reason'],
    		    );
            }else{
                $updateInfo = array('fullminus_status' => $apiData['status']);
            }

            $result = $objMdlPromotions->update(array('check_status'=>$apiData['status']),$filter2); 
            
            if (!$objMdlfullminus->update($updateInfo,$filter) && !$result) {
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

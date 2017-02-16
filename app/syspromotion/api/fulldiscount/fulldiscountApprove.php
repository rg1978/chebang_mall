<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * fulldiscount审核接口
 * promotion.fulldiscount.approve
 */
final class syspromotion_api_fulldiscount_fulldiscountApprove {

    public $apiDescription = '满折审核';

    /**
     *  满折审核参数
     * @desc 满折审核参数
     * @param  array $apiData 满折审核参数
     * @return bool true 
     */
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'', 'title'=>'店铺ID', 'example'=>'', 'desc'=>'店铺ID'],
            'fulldiscount_id' => ['type'=>'int', 'valid'=>'', 'title'=>'满折规则id', 'example'=>'', 'desc'=>'满折规则id'],
            'status' => ['type'=>'string', 'valid'=>'in:agree,refuse,non-reviewed,pending', 'title'=>'审核状态', 'example'=>'', 'desc'=>'审核状态'],
            'reason' => ['type'=>'string', 'valid'=>'required_if:status,refuse', 'title'=>'驳回原因', 'example'=>'', 'desc'=>'驳回原因']
        );

        return $return;
    }

    /**
     *  满折审核
     * @desc 用于满折审核
     * @return bool true
     */
    public function approve($apiData)
    {
        $objMdlfulldiscount = app::get('syspromotion')->model('fulldiscount');
        $objMdlPromotions = app::get('syspromotion')->model('promotions');

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if($apiData['shop_id'] && $apiData['fulldiscount_id']){
                $filter = array('shop_id'=>$apiData['shop_id'],'fulldiscount_id'=>$apiData['fulldiscount_id']);
            }else{
                $filter =array('fulldiscount_status|in' => array('non-reviewed','pending'));
            }

            if($apiData['fulldiscount_id']){
                $filter2 = array('rel_promotion_id' => $apiData['fulldiscount_id'],'promotion_type'=>'fulldiscount');
            }else{
                $filter2 = array(
                    'promotion_type'=>'fulldiscount',
                    'check_status|in' => array('non-reviewed','pending'));
            }

            if ($apiData['status'] == 'refuse') {
                $updateInfo = array(
                    'fulldiscount_status' => $apiData['status'],
                    'reason' => $apiData['reason'],
                );
            }else{
                $updateInfo = array('fulldiscount_status' => $apiData['status'],);
            }

            $result = $objMdlPromotions->update(array('check_status'=>$apiData['status']),$filter2); 

            if (!$objMdlfulldiscount->update($updateInfo,$filter) && !$result) {
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

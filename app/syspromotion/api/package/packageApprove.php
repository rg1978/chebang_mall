<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * package审核接口
 * promotion.package.approve
 */
final class syspromotion_api_package_packageApprove {

    public $apiDescription = '组合促销审核';

    /**
     *  组合促销审核参数
     * @desc 组合促销审核参数
     * @param  array $apiData 组合促销参数
     * @return bool true 
     */
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'', 'title'=>'店铺ID', 'example'=>'', 'desc'=>'店铺ID'],
            'package_id' => ['type'=>'int', 'valid'=>'', 'title'=>'满折规则id', 'example'=>'', 'desc'=>'满折规则id'],
            'status' => ['type'=>'string', 'valid'=>'in:agree,refuse,non-reviewed,pending', 'title'=>'审核状态', 'example'=>'', 'desc'=>'审核状态'],
            'reason' => ['type'=>'string', 'valid'=>'required_if:status,refuse', 'title'=>'驳回原因', 'example'=>'', 'desc'=>'驳回原因']
        );

        return $return;
    }

    /**
     *  组合促销审核
     * @desc 用于组合促销审核
     * @return bool true
     */
    public function approve($apiData)
    {
        $objMdlpackage = app::get('syspromotion')->model('package');
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if($apiData['shop_id'] && $apiData['package_id']){
                $filter = array('shop_id'=>$apiData['shop_id'],'package_id'=>$apiData['package_id']);
            }else{
                $filter =array('package_status|in' => array('non-reviewed','pending'));
            }
            
            if ($apiData['status'] == 'refuse') {
    	       $updateInfo = array(
    	           'package_status' => $apiData['status'],
    	           'reason' => $apiData['reason'],
    		    );
            }else{
                $updateInfo = array('package_status' => $apiData['status'],);
            }
            if (!$objMdlpackage->update($updateInfo,$filter)) {
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

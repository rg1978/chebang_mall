<?php
/**
 * 更新促销审核状态
 *
 * @author     hlj
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_events_listeners_promotionApprove {
	public $status = 'agree';

	public function approve($apiData)
    {
    	$apiData = array(
    		'status' => $this->status,
    	);

        $couponApproveStatus = app::get('syspromotion')->rpcCall('promotion.coupon.approve',$apiData);
        $xydiscountApproveStatus = app::get('syspromotion')->rpcCall('promotion.xydiscount.approve',$apiData);
        $fulldiscountApproveStatus = app::get('syspromotion')->rpcCall('promotion.fulldiscount.approve',$apiData);
        $fullminusApproveStatus = app::get('syspromotion')->rpcCall('promotion.fullminus.approve',$apiData);
        $packageApproveStatus = app::get('syspromotion')->rpcCall('promotion.package.approve',$apiData);
        $packageApproveStatus = app::get('syspromotion')->rpcCall('promotion.gift.approve',$apiData);
    }
}
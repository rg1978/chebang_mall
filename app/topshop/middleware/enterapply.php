<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
/**
 *
 * 检查该帐号是否能够进行入住申请
 */
class topshop_middleware_enterapply
{

    public function handle($request, Closure $next)
    {
        #//获取shopInfo
        $sellerId = pamAccount::getAccountId();
        $shopId = app::get('topshop')->rpcCall('shop.get.loginId',array('seller_id'=>$sellerId),'seller');
        //已有店铺则不需要进入入住申请路由
        if( $shopId )
        {
            return redirect::action('topshop_ctl_index@index');
        }
        return $next($request);
    }
}


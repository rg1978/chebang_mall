<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_middleware_developerMode
{

    public function handle($request, Closure $next)
    {
        //获取shopInfo
        $sellerId = pamAccount::getAccountId();
        $shopId = app::get('topshop')->rpcCall('shop.get.loginId',array('seller_id'=>$sellerId),'seller');

        $requestParams = ['shop_id'=>$shopId];
        $shopConf = app::get('topshop')->rpcCall('open.shop.develop.conf', $requestParams);
        if($shopConf['develop_mode'] == 'DEVELOP')
        {
            if( request::ajax() && input::get('response') != 'html')
            {
                return response::json(array(
                    'error' => true,
                    'message'=> '当前为开发者模式，该功能已被接管',
                ));
            }
            return redirect::action('topshop_ctl_index@nopermission');
        }
        return $next($request);
    }

}


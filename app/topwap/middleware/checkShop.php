<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topwap_middleware_checkShop
{
    public function handle($request, Closure $next)
    {
        $shopId = intval(input::get('shop_id'));
        $isAjax = request::ajax();
        
        if( !$shopId  )
        {
            return $this->__isAjax($isAjax);
        }

        $shopdata = app::get('topwap')->rpcCall('shop.get',array('shop_id'=>$shopId));
        
        if( empty($shopdata))
        {
            return $this->__isAjax($isAjax);
        }

        return $next($request);
    }
    
    private function __isAjax($isAjax)
    {
        if( $isAjax )
        {
            $url = url::action('topwap_ctl_default@index');
            $data['error'] = true;
            $data['redirect'] = $url;
            $data['message'] = app::get('topwap')->_('店铺不存在');
            return response::json($data);exit;
        }
        
        return redirect::action('topwap_ctl_default@index');
    }
}

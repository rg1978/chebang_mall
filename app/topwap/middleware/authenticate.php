<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topwap_middleware_authenticate
{

    public function handle($request, Closure $next)
    {
        if( !userAuth::check() )
        {
            if( request::ajax() )
            {
                $url = url::action('topwap_ctl_passport@goLogin');
                $data['error'] = true;
                $data['redirect'] = $url;
                $data['message'] = app::get('topwap')->_('请登录');
                return response::json($data);exit;
            }
            
            return redirect::action('topwap_ctl_passport@goLogin');
        }
        return $next($request);
    }
}

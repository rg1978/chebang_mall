<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topwap_middleware_redirectIfAuthenticated
{

    public function handle($request, Closure $next)
    {
        if (userAuth::check())
        {
            return redirect::route('wap.home');
        }
        return $next($request);
    }
}

<?php

/**
 * 检测用户是否登录注册成功，在路由中调用此中间件
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_middleware_redirectIfAuthenticated {

    public function __construct()
    {

    }

    public function handle($request, Closure $next)
    {

        //判断用户是否登录注册成功
        pamAccount::setAuthType('sysshop');
        if(pamAccount::check()){

            return redirect::route('topshop.home');
        }

        return $next($request);
    }

}

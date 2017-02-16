<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_middleware_permission
{
    public function handle($request, Closure $next)
    {
        $routeAs = route::currentRouteName();
        //如果没有登录
        pamAccount::setAuthType('sysshop');
        if( !pamAccount::check() && !in_array($routeAs,config::get('permission.common.nologin')) )
        {
            if( request::ajax() )
            {
                return redirect::action('topshop_ctl_passport@simpleSignin');
            }

            return redirect::action('topshop_ctl_passport@signin');
        }

        $currentPermission = shopAuth::getSellerPermission();
        //$currentPermission = false 表示为店主不用判断权限

        //获取当前用户的路由权限
        if( $currentPermission && !in_array($routeAs, $currentPermission) )
        {
            if( request::ajax() )
            {
                return response::json(array(
                    'error' => true,
                    'message'=> '无操作权限',
                ));
            }

            return redirect::action('topshop_ctl_index@nopermission');
        }

        return $next($request);
    }

}


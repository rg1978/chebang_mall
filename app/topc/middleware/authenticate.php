<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_middleware_authenticate
{

    public function __construct()
    {

    }

    public function handle($request, Closure $next)
    {

        // 检测是否登录
        if( !userAuth::check() )
        {
            $nextPage = request::fullurl();

            if( request::ajax() )
            {
                $url = url::action('topc_ctl_passport@signin');
                return response::json(array(
                    'error' => true,
                    'message' => '未登录请进行登录',
                    'redirect' => $url,
                ));
            }

            return redirect::action('topc_ctl_passport@signin', array('next_page' => $nextPage));
        }
        return $next($request);
    }
}

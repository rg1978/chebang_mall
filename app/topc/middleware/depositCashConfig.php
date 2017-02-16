<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_middleware_depositCashConfig
{

    public function __construct()
    {

    }

    public function handle($request, Closure $next)
    {
        $cashConfig = app::get('topc')->rpcCall('user.deposit.getCashConf');

        if( $cashConfig['depositCash'] == 0 )
        {
            return redirect::action('topc_ctl_member_deposit@view');
        }
        return $next($request);
    }
}

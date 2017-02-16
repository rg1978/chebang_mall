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
class topshop_middleware_register
{

    public function handle($request, Closure $next)
    {
        $action = route::currentActionName();
        $actionArr = explode('@',$action);
        $method = $actionArr[1];

        $sellerId = pamAccount::getAccountId();
        $datalist = app::get('topshop')->rpcCall('shop.get.enterapply',array('seller_id'=>$sellerId,'fields'=>'status'));
        $status = $datalist['status'];

        if($status == 'active' && !in_array($method, ['enterProcessWaiteExamine']))
            return redirect::action('topshop_ctl_register@enterProcessWaiteExamine');

        if($status == 'successful' && !in_array($method, ['enterProcessWaiteAward']))
            return redirect::action('topshop_ctl_register@enterProcessWaiteAward');

        if($status == 'failing' && !in_array($method, [
                'enterProcessWaiteAward',
                'enterProcessUpdateApply',
                'enterProcessCompanyInfo',
                'enterProcessCompanyInfoAction',
                'enterProcessEconomicInfo',
                'enterProcessEconomicInfoAction',
                'enterProcessShopInfo',
                'enterProcessShopInfoAction', ]))
            return redirect::action('topshop_ctl_register@enterProcessWaiteAward');

        if($status == 'finish')
            return redirect::action('topshop_ctl_index@index');

        return $next($request);
    }
}


<?php
/**
 * ShopEx licence
 * - trade.update.hongbao.money
 * - 更新订单使用红包支付金额
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class systrade_api_trade_updateHongbaoPayMoney {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新订单使用红包支付金额';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int',    'valid'=>'required',  'title'=>'用户ID',   'desc'=>'用户ID'],
            'tid'     => ['type'=>'string', 'valid'=>'required',  'title'=>'订单号',   'desc'=>'订单号'],
            'money'   => ['type'=>'string', 'valid'=>'required',  'title'=>'红包金额', 'desc'=>'红包金额'],
            'user_hongbao_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'用户红包列表ID',    'desc'=>'用户红包列表ID,如果为叠加使用则用逗号(,)隔开'],
        );
        return $return;
    }

    /**
     * 更新订单使用红包支付金额
     *
     * @desc 更新订单使用红包支付金额
     * @return bool result
     */
    public function update($params)
    {
        return app::get('systrade')->model('trade')->update(['hongbao_fee'=>$params['money'],'user_hongbao_id'=>$params['user_hongbao_id']],['tid'=>$params['tid'],['user_id']=>$params['user_id']]);
    }
}

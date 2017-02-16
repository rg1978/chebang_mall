<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysim_api_webcall_edit
{
    /**
     * 接口作用说明
     * trade.cart.cartCouponAdd
     */
    public $apiDescription = '365webcall专用，保存商家365webcall配置信息';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        return [
            'params'=>[
                'shop_id' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'1','description'=>'店铺id'],
                'email' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'417855097@qq.com','description'=>'365webcall邮箱账户'],
                'use_im' => ['type'=>'int', 'valid'=>'required', 'default'=>'0', 'example'=>'1','description'=>'是否开启365webcall'],
            ]
        ];


    }

    /**
     *
     * @return bool result 标记是否保存成功
     */
    public function save($params)
    {

        $wc = [];
        $wc['shop_id'] = $params['shop_id'];
        $wc['email']   = $params['email'];
        $wc['use_im']  = $params['use_im'] == '1' || $params['use_im'] == 1 ? 1 : 0;

        $webcallMdl = app::get('sysim')->model('account_webcall_shop');
        $webcallMdl->save($wc);

        return true;

    }



}



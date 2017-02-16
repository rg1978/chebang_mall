<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysim_api_webcall_get
{
    /**
     * 接口作用说明
     * trade.cart.cartCouponAdd
     */
    public $apiDescription = '365webcall专用，获取商家365webcall配置信息';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
            return [
            'params'=>[
                'shop_id' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'1','description'=>'店铺id'],
                'fields' => ['type'=>'string','valid'=>'required','default'=>'','example'=>'shop_id,shop_name','description'=>'365webcall数据（详情请看数据字典）'],
            ]
        ];


    }

    /**
     *
     * @return string shop_id 店铺id（platform表示平台）
     * @return string email 365webcall账号
     * @return bool use_im 是否开启webcall的使用配置
     */
    public function get($params)
    {
        $shop_id = $params['shop_id'];
        $fields = $params['fields'];

        $webcallMdl = app::get('sysim')->model('account_webcall_shop');
        $wc = $webcallMdl->getRow($fields, ['shop_id'=>$shop_id]);

        return $wc;
    }

}

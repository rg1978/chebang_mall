<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysim_api_webcall_signUp
{
    /**
     * 接口作用说明
     * trade.cart.cartCouponAdd
     */
    public $apiDescription = '注册365webcall账号';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
            return [
            'params'=>[
                'shop_id' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'1','description'=>'店铺id'],
                'email' => ['type'=>'string', 'valid'=>'required|email', 'default'=>'', 'example'=>'shopex@shopex.cn','description'=>'邮箱'],
                'pwd' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'admin123','description'=>'密码'],
                'name' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'Elrond','description'=>'名称'],
                'area' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'上海市 徐汇区','description'=>'二级地区 以空格分隔'],
                'url' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'http://www.shopex.cn','description'=>'公司网址'],
                'corpName' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'商派软件有限公司','description'=>'公司名称'],
                'phone' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'+86 021 33251818','description'=>'电话号码'],
                'qq' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'999999999','description'=>'qq号码'],
                'contact' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'张三','description'=>'联系人'],
                'use_im' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'1','description'=>'是否启用']
            ]
        ];
    }

    /**
     *
     * @return int accountId 返回webcall的自增量id
     */
    public function sign($params)
    {
        $shop_id = $params['shop_id'];
        $email = $params['email'];
        $pwd = $params['pwd'];
        $name = $params['name'];
        $url = $params['url'];
        $area = $params['area'];
        $corpName = $params['corpName'];
        $phone = $params['phone'];
        $qq = $params['qq'];
        $contact = $params['contact'];
        $useIm = $params['use_im'];

        return kernel::single('sysim_webcall_webcall')->addAccount($shop_id, $email, $pwd, $name, $url, $area, $corpName, $phone, $qq, $contact, $useIm);
    }

}

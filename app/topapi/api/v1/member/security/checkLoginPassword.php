<?php
/**
 * topapi
 *
 * -- member.security.checkLoginPassword
 * -- 安全中心验证登录密码
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_security_checkLoginPassword implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '安全中心验证登录密码';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'password' => ['type'=>'string','valid'=>'required', 'desc'=>'登录用户密码','example'=>'', 'msg'=>'请填写原登录密码'],
        );

        return $return;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":null}';
    }

    public function handle($params)
    {
        if( app::get('topwap')->rpcCall('user.login.pwd.check',$params) )
        {
            $key = 'topapi'.$params['user_id'].'security-update-password';
            cache::store('vcode')->put($key, true, 300);
            return true;
        }
        else
        {
            throw new \LogicException('密码错误');
        }
    }
}

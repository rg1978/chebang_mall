<?php
/**
 * topapi
 *
 * -- member.security.updatePayPassword
 * -- 安全中心修改支付密码
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_security_updatePayPassword implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '安全中心修改支付密码';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'password'              => ['type'=>'string', 'valid'=>'required|min:6|max:20|confirmed', 'example'=>'demo123', 'desc'=>'新密码', 'msg'=>'请输入密码|密码长度不能小于6位|密码长度不能大于20位|输入的密码不一致'],
            'password_confirmation' => ['type'=>'string', 'valid'=>'required', 'example'=>'demo123', 'desc'=>'确认新密码',   'msg'=>'请填写确认密码'],
        );

        return $return;
    }

    public function returnJson()
    {
        return '';
    }

    public function handle($params)
    {
        $this->checkPassword($params['password']);

        $key = 'topapi'.$params['user_id'].'security-check-pay-password';
        if( ! cache::store('vcode')->get($key) )
        {
            throw new \LogicException('页面已过期，请重新验证支付密码');
        }
        cache::store('vcode')->put($key, false);

        $requestParams = [
            'user_id'=>$params['user_id'],
            'password'=>$params['password']
        ];
        return app::get('topapi')->rpcCall('user.deposit.password.set', $requestParams);
    }

    private function checkPassword($newPassword)
    {
        $a = 0;
        if(preg_match("/(?=.*[0-9])[a-zA-Z0-9]{6,20}/", $newPassword))
        {
            $a += 1;
        }

        if(preg_match("/(?=.*[a-z])[a-zA-Z0-9]{6,20}/", $newPassword))
        {
            $a += 1;
        }

        if(preg_match("/(?=.*[A-Z])[a-zA-Z0-9]{6,20}/", $newPassword))
        {
            $a += 1;
        }

        if($a >= 2)
        {
            return true;
        }

        throw new LogicException('密码格式错误,请参考密码规则');
    }
}

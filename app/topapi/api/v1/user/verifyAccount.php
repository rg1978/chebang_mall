<?php
/**
 * topapi
 *
 * -- user.verifyAccount
 * -- 验证注册账号
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_verifyAccount implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '验证注册账号';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'account'    => ['type'=>'string', 'valid'=>'required', 'example'=>'demo',          'desc'=>'用户名／手机／邮箱', 'msg'=>''],
            'vcode_type' => ['type'=>'string', 'valid'=>'required', 'example'=>'topapi-signin', 'desc'=>'图片验证类型',      'msg'=>''],
            'verifycode' => ['type'=>'string', 'valid'=>'required', 'example'=>'xuab',          'desc'=>'图片验证码',       'msg'=>''],
        ];
    }

    /**
     * @return string account 注册账号
     * @return sring type login_account | email | mobile
     * @return sring signup_token 验证账号后返回给短信或者设置密码时的令牌
     */
    public function handle($data)
    {
        $uname = $data['account'];
        $accountType = app::get('topapi')->rpcCall('user.get.account.type',array('user_name'=>$uname));

        kernel::single('sysuser_passport')->checkSignupAccount($uname, $accountType);

        $verifycode = $data['verifycode'];
        if( !base_vcode::verify($data['vcode_type'], $verifycode))
        {
            throw new \LogicException(app::get('topapi')->_('验证码填写错误'));
        }

        $randomId = str_random(32);
        cache::store('vcode')->put('topapi'.$uname.'topapi-signup', $randomId, 3600);

        $res['account'] = $uname;
        $res['type'] = $accountType;
        $res['signup_token'] = $randomId;

        return $res;
    }
}


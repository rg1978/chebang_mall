<?php
/**
 * topapi
 *
 * -- user.login
 * -- 用户登录
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_login implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户登录';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'account'  => ['type'=>'string', 'valid'=>'required', 'example'=>'demo',    'desc'=>'登录账号/手机/邮箱', 'msg'=>'请填写登录账号'],
            'password' => ['type'=>'string', 'valid'=>'required', 'example'=>'demo123', 'desc'=>'登录密码',         'msg'=>'请填写密码'],
            'deviceid' => ['type'=>'string', 'valid'=>'required', 'example'=>'demo123', 'desc'=>'用户设备',         'msg'=>''],
            'vcode'    => ['type'=>'string', 'valid'=>'',         'example'=>'',        'desc'=>'登录超过3次，出现图片验证码，需要验证图片验证码', 'msg'=>''],
        ];
    }

    /**
     * @return int userId 用户ID
     * @return string accessToken 注册完成后返回的accessToken
     */
    public function handle($params)
    {
        $account = $params['account'];
        $password = $params['password'];

        $result['user_id'] = app::get('topapi')->rpcCall('user.login', ['user_name' => $account, 'password' => $password]);

        $data['account'] = $account;
        $data['password'] = $password;
        $data['deviceid'] = $params['deviceid'];
        $result['accessToken'] = kernel::single('topapi_token')->make($result['user_id'], $data);

        return $result;
    }
}


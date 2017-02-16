<?php
/**
 * topapi
 *
 * -- user.verifySms
 * -- 验证短信
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_verifySms implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '验证短信';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'mobile' => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918087654', 'desc'=>'手机号', 'msg'=>''],
            'type'   => ['type'=>'string', 'valid'=>'required',        'example'=>'',            'desc'=>'验证发送短信类型 topapi-signup 注册发送短信', 'msg'=>''],
            'vcode'  => ['type'=>'string', 'valid'=>'required',        'example'=>'',            'desc'=>'短信验证码', 'msg'=>''],
        ];
    }

    public function handle($params)
    {
        if( !userVcode::verify($params['vcode'], $params['mobile'], 'topapi-singup') )
        {
            throw new \LogicException(app::get('topapi')->_('验证码输入错误'));
        }

        $signupToken = cache::store('vcode')->get('topapi'.$params['mobile'].$data['type']);
        $res['signup_token'] = $signupToken;

        return $res;
    }
}


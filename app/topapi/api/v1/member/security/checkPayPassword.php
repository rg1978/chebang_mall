<?php
/**
 * topapi
 *
 * -- member.security.checkPayPassword
 * -- 安全中心验证支付密码
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_security_checkPayPassword implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '安全中心验证支付密码';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'pay_password'  => ['type'=>'string', 'valid'=>'required', 'example'=>'demo123', 'desc'=>'支付密码', 'msg'=>'请输入支付密码'],
        );

        return $return;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":null}';
    }

    public function handle($params)
    {
        $requestParams['user_id'] = $params['user_id'];
        $requestParams['old_password'] = $params['pay_password'];

        // 开始验证密码
        $resutl = app::get('topapi')->rpcCall('user.check.deposit.oldpwd', $requestParams);
        if( !$resutl )
        {
            throw new \LogicException('支付密码错误');
        }

        $key = 'topapi'.$params['user_id'].'security-check-pay-password';
        cache::store('vcode')->put($key, true, 300);

        return true;
    }
}

<?php
/**
 * topapi
 *
 * -- member.setAccount
 * -- 设置用户名
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_setAccount implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '设置用户名';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'account'   => ['type'=>'string', 'valid'=>'required|loginaccount|min:4|max:30','example'=>'demo', 'desc'=>'登录用户名', 'msg'=>'请填写用户名|不能为纯数字或邮箱地址|最少4个字符|用户名过长,请换一个重试'],
        ];
    }

    /**
     * @return bool true 成功
     */
    public function handle($params)
    {
        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$params['user_id']]);
        if( $userInfo['login_account'] )
        {
            throw new LogicException(app::get('topapi')->_('无需设置'));
        }

        $data = array(
            'user_name' => $params['account'],
            'user_id'   => $params['user_id'],
        );

        return app::get('topapi')->rpcCall('user.account.update',$data);
    }
}


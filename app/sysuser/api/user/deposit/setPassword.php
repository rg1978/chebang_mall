<?php
class sysuser_api_user_deposit_setPassword{
    public $apiDescription = "修改预存款密码的接口（不需要旧密码）";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'password' => ['type'=>'string','valid'=>'required', 'description'=>'会员预存款支付密码','default'=>'','example'=>'']
        );
        return $return;
    }

    public function setPassword($params)
    {

        $userId = $params['user_id'];
        $password = $params['password'];

        // 验证支付密码和登录密码是否一致
        $checkData = array('user_id'=>$userId, 'cipher'=>$password, 'check_type'=>'deposit');
        app::get('sysuser')->rpcCall('user.check.loginPwd.DepositPwd',$checkData);
        
        $deposit = kernel::single('sysuser_data_deposit_password')->setPassword($userId, $password);

        return ['result'=>true];
    }
}


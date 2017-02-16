<?php
class sysuser_api_user_deposit_changePassword{
    public $apiDescription = "修改预存款密码的接口（需要旧密码）";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'old_password' => ['type'=>'string','valid'=>'required', 'description'=>'原有的会员预存款支付密码','default'=>'','example'=>''],
            'new_password' => ['type'=>'string','valid'=>'required', 'description'=>'新的会员预存款支付密码','default'=>'','example'=>''],
        );
        return $return;
    }

    public function changePassword($params)
    {

        $userId = $params['user_id'];
        $oldPassword = $params['old_password'];
        $newPassword = $params['new_password'];

        // 验证支付密码和登录密码是否一致
        $checkData = array('user_id'=>$userId, 'cipher'=>$newPassword, 'check_type'=>'deposit');
        app::get('sysuser')->rpcCall('user.check.loginPwd.DepositPwd',$checkData);
        
        $deposit = kernel::single('sysuser_data_deposit_password')->changePassword($userId, $oldPassword, $newPassword);

        return ['result'=>true];
    }
}


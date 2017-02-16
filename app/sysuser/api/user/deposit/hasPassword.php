<?php
class sysuser_api_user_deposit_hasPassword{
    public $apiDescription = "判断会员是否有预存款密码";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
        );
        return $return;
    }

    //@return bool result true有密码，false无密码
    public function hasPassword($params)
    {

        $userId = $params['user_id'];

        $deposit = kernel::single('sysuser_data_deposit_password')->hasPassword($userId);

        return ['result'=>$deposit];
    }
}


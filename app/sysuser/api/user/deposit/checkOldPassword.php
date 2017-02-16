<?php

/**
 * checkOldPassword.php 
 * - user.check.deposit.oldpwd
 * - 验证预存款原支付密码
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_api_user_deposit_checkOldPassword {
    public $apiDescription = "验证预存款原支付密码";
    
    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
                'user_id' => ['type'=>'int','valid'=>'required', 'title'=>'会员id','desc'=>'会员id','example'=>''],
                'old_password' => ['type'=>'string','valid'=>'required', 'title'=>'预存款支付密码','desc'=>'原有的会员预存款支付密码','example'=>''],
        );
        return $return;
    }
    
    /**
     * 验证预存款原支付密码
     * @desc 用于验证预存款原支付密码
     * @return bool result 验证结果
     */
    
    public function checkOldPwd($params)
    {
        $userId = $params['user_id'];
        $oldPassword = $params['old_password'];
        $deposit = kernel::single('sysuser_data_deposit_password')->checkPassword($userId, $oldPassword);
        
        return ['result'=>true];
    }

}
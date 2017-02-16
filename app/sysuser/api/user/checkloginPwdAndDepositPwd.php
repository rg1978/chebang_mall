<?php
/**
 * checkPwd.php 
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class sysuser_api_user_checkloginPwdAndDepositPwd{
    
    public $apiDescription = "验证会员登录密码和支付密码是否一致";
    
    public function getParams()
    {
        $return['params'] = array(
                'user_id' => ['type'=>'int','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
                'cipher' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'密码明文'],
                'check_type' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'验证类型'],
        );
        return $return;
    }
    
    public function checkpwd($params)
    {
        $arr = array('account', 'deposit');
        if(!in_array($params['check_type'], $arr))
        {
            throw new LogicException(app::get('sysuser')->_('验证类型错误'));
        }
        
        switch ($params['check_type']) {
            case 'account':
                return kernel::single('sysuser_data_deposit_password')->checkLogpwdWithDepositpwd(intval($params['user_id']), $params['cipher']);
            break;
            
            case 'deposit':
                return kernel::single('sysuser_passport')->checkDepositpwdWithLogpwd(intval($params['user_id']), $params['cipher']);
            break;
        }
    }
}
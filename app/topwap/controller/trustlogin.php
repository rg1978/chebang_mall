<?php
class topwap_ctl_trustlogin extends topwap_controller{

    public function __construct()
    {
        parent::__construct();
        kernel::single('base_session')->start();
    }

	/**
	 * callback返回页, 同时是bind页面
	 *
	 * @return base_http_response
	 */
    public function callback()
    {
        $params = input::get();
        $flag = $params['flag'];
        unset($params['flag']);

        // 信任登陆校验
        $userTrust = kernel::single('pam_trust_user');
        $res = $userTrust->authorize($flag, 'web', 'topwap_ctl_trustlogin@callback', $params);

        $binded = $res['binded'];
        $userinfo = $res['user_info'];
        $realname = $userinfo['nickname'];
        $avatar = $userinfo['figureurl'];

        if ($binded)
        {
            $userId = $res['user_id'];

            userAuth::login($userId);
            return redirect::action('topwap_ctl_default@index');
        }
        else
        {
            $pagedata['realname'] =  $realname;
            $pagedata['avatar'] = $avatar;

            $pagedata['flag'] = $flag;
            return $this->page('topwap/trustlogin/bind.html', $pagedata);
        }
    }

    // public function bindDefaultCreateUser()
    // {
    //     $params = input::get();
    //     $flag = $params['flag'];
    //     try
    //     {
    //         $userId = kernel::single('pam_trust_user')->bindDefaultCreateUser($flag);
    //         userAuth::login($userId, $loginName);
    //         //redirect::action('topwap_ctl_default@index')->send();exit;
    //         $url = url::action('topwap_ctl_default@index');
    //         return $this->splash('success', $url, $msg, true);

    //     }
    //     catch (\Exception $e)
    //     {
    //         $msg = $e->getMessage();
    //         return $this->splash('error',null,$msg,true);
    //     }
    // }

    public function bindExistsUser()
    {
        $params = input::get();
        $verifyCode = $params['verifycode'];
        $verifyKey = $params['vcodekey'];
        $loginName = $params['uname'];
        $password = $params['password'];

         if(!$loginName || !$password )
         {
            $msg = app::get('topwap')->_('用户名或密码必填') ;
            return $this->splash('error', $url, $msg, true);
         }

        if(userAuth::isShowVcode('login')){
            if( (!$verifyKey) || $b=empty($verifyCode) || $c=!base_vcode::verify($verifyKey, $verifyCode))
            {
                $msg = app::get('topwap')->_('验证码填写错误') ;
                $url = 'vcode_is_show';
                return $this->splash('error', $url, $msg, true);
            }
        }

        try
        {
            if (userAuth::attempt($loginName, $password))
            {
                kernel::single('pam_trust_user')->bind(userAuth::id());
                $url = url::action('topwap_ctl_default@index');
                return $this->splash('success', $url, $msg, true);
            }
        }
        catch (Exception $e)
        {
            userAuth::setAttemptNumber();
            if( userAuth::isShowVcode('login') )
            {
                $url = 'vcode_is_show';
            }
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
    }

    public function bindSignupUser()
    {
        $params = input::get();
        $verifyCode = $params['verifycode'];
        $verifyKey =  $params['vcodekey'];
        $loginName = $params['pam_account']['login_name'];
        $password = $params['pam_account']['login_password'];
        $confirmedPassword = $params['pam_account']['psw_confirm'];

        if(!$loginName)
         {
            $msg = app::get('topwap')->_('用户名必填') ;
            return $this->splash('error', $url, $msg, true);
         }
         
         if(!$password)
         {
            $msg = app::get('topwap')->_('密码必填') ;
            return $this->splash('error', $url, $msg, true);
         }

         if(!$confirmedPassword)
         {
            $msg = app::get('topwap')->_('确认密码必填') ;
            return $this->splash('error', $url, $msg, true);
         }


        if( !$verifyKey || empty($verifyCode) || !base_vcode::verify($verifyKey, $verifyCode))
        {
            $msg = app::get('topwap')->_('验证码填写错误') ;
            return $this->splash('error', null, $msg, true);
        }

        try
        {
            $userId = userAuth::signUp($loginName, $password, $confirmedPassword);
            userAuth::login($userId, $loginName);
            kernel::single('pam_trust_user')->bind(userAuth::id());

            $url = url::action('topwap_ctl_default@index');
            return $this->splash('success', $url, $msg, true);
        }
        catch (\Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
    }
}

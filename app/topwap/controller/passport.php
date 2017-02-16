<?php
class topwap_ctl_passport extends topwap_controller{

    public function __construct()
    {
        parent::__construct();
        kernel::single('base_session')->start();
        $this->passport = kernel::single('topwap_passport');
    }

    /**
     * @brief 进入登录页面
     *
     * @return
     */
    public function goLogin()
    {
        $next_page = $this->__getFromUrl();

        if (kernel::single('pam_trust_user')->enabled())
        {
            $trustInfoList = kernel::single('pam_trust_user')->getTrustInfoList('wap', 'topwap_ctl_trustlogin@callback');
        }

        $isShowVcode = userAuth::isShowVcode('login');
        $pagedata = compact('trustInfoList','isShowVcode','next_page');
        return $this->page('topwap/passport/login/index.html',$pagedata);
    }

    /**
     * @brief 完成登录流程
     *
     * @return
     */
    public function doLogin()
    {
        if(userAuth::isShowVcode('login') )
        {
            $url = url::action('topwap_ctl_default@index');
            $verifycode = input::get('verifycode');
            if( !input::get('key') || empty($verifycode) || !base_vcode::verify(input::get('key'), $verifycode))
            {
                $msg = app::get('topwap')->_('验证码填写错误') ;
                return $this->splash('error',null,$msg);
            }
        }

        try
        {
            //记住密码功能暂无
            //userAuth::setAttemptRemember(input::get('remember',null));

            if (userAuth::attempt(input::get('account'), input::get('password')))
            {
                //商品收藏店铺收藏加入cookie
                $userId = userAuth::id();
                $collectData = app::get('topwap')->rpcCall('user.collect.info',array('user_id'=>$userId));
                setcookie('collect',serialize($collectData));
                $url = $url ?:$this->__getFromUrl();
                kernel::single('topwap_cart')->mergeCart();
                return $this->splash('success',$url,$msg);
            }
        }
        catch(Exception $e)
        {
            userAuth::setAttemptNumber();
            if( userAuth::isShowVcode('login') )
            {
                $url = url::action('topwap_ctl_passport@goLogin');
            }
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
    }


    /**
     * @brief 进入注册页面
     *
     * @return
     */
    public function goRegister()
    {
        if( userAuth::check() ) $this->logout();
        return $this->page('topwap/passport/register/index.html');
    }

    /**
     * @brief 注册时验证用户名是否有效
     *
     * @return
     */
    public function checkUname()
    {
        $data = utils::_filter_input(input::get());

        $uname = $data['uname'];
        $userData = userAuth::getAccountInfo($uname);
        if($userData)
        {
            $msg = app::get('topwap')->_("该用户名或手机号已经使用");
            return $this->splash('error','',$msg);
        }

        $accountType = app::get('topwap')->rpcCall('user.get.account.type',array('user_name'=>$uname));
        try
        {
            kernel::single('sysuser_passport')->checkSignupAccount($uname, $accountType);
        }
        catch( \LogicException $e )
        {
            return $this->splash('error','',$e->getMessage());
        }

        //检测注册协议是否被阅读选中
        if(!input::get('license'))
        {
            $msg = app::get('topwap')->_('请阅读并接受会员注册协议');
            return $this->splash('error','',$msg);
        }

        $verifycode = $data['verifycode'];
        if( !input::get('key') || empty($verifycode) || !base_vcode::verify(input::get('key'), $verifycode))
        {
            $msg = app::get('topwap')->_('验证码填写错误') ;
            return $this->splash('error',null,$msg);
        }

        if($accountType == "mobile")
        {
            $pagedata['data']['mobile'] = $uname;
            $pagedata['data']['type'] = 'signup';
            return view::make('topwap/passport/verify_vcode.html',$pagedata);
        }
        else
        {
            return view::make('topwap/passport/register/set_pwd.html',$data);
        }
    }

    /**
     * @brief 完成注册流程
     *
     * @return
     */
    public function doRegister()
    {
        $data = utils::_filter_input(input::get());
        $codyKey = $data['key'];
        $userInfo = $data['pam_user'];
        $validator = validator::make(
            ['loginAccount'=>$userInfo['account'],'password' => $userInfo['password'], 'password_confirmation' => $userInfo['pwd_confirm']],
            ['loginAccount'=>'required','password' => 'min:6|max:20|confirmed','password_confirmation'=>'required'],
            ['loginAccount'=>'请输入用户名!','password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!','password_confirmation'=>'确认密码不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0],true);
            }
        }
        try
        {
            $userId = userAuth::signUp($userInfo['account'], $userInfo['password'], $userInfo['pwd_confirm']);
            userAuth::login($userId, $userInfo['account']);
        }
        catch(Exception $e)
        {
            //$url = url::action('topwap_ctl_passport@goRegister');
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        $pagedata['site_name'] = app::get('site')->getConf('site.name');
        $pagedata['site_logo'] = app::get('site')->getConf('site.logo');

        $url = url::action('topwap_ctl_passport@registerSucc');
        return $this->splash('success',$url,$msg,true);
    }

    public function registerSucc()
    {
        $pagedata['site_name'] = app::get('site')->getConf('site.name');
        $pagedata['site_logo'] = app::get('site')->getConf('site.logo');
        $pagedata['sendPointNum'] = app::get('sysconf')->getConf('sendPoint.num');
        $pagedata['open_sendpoint'] = app::get('sysconf')->getConf('open.sendPoint');
        return $this->page('topwap/passport/register/succ.html',$pagedata);
    }

    /**
     * @brief 获取用户注册协议
     *
     * @return
     */
    public function registerLicense()
    {
        $pagedata['title'] = "用户注册协议";
        $licence = app::get('sysconf')->getConf('sysconf_setting.wap_license');
        if($licence)
        {
            $pagedata['license'] = $licence;
        }
        else
        {
            $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_user_license');
        }
        return $this->page('topwap/passport/register/license.html', $pagedata);
    }



    /**
     * @brief 找回密码第一步，进入找回密码页面
     *
     * @return  html
     */
    public function goFindPwd()
    {
        return $this->page('topwap/passport/forgotten/verify-uname.html');
    }

    /**
     * @brief 找回密码第二步，验证用户名/手机号
     *
     * @return
     */
    public function verifyUsername()
    {
        $postdata = utils::_filter_input(input::get());

        //验证图片验证码
        $valid = validator::make(
            [$postdata['verifycode']],['required']
        );
        if($valid->fails())
        {
            return $this->splash('error',null,"图片验证码不能为空!");
        }
        if(!base_vcode::verify($postdata['verifycodekey'],$postdata['verifycode']))
        {
            return $this->splash('error',null,"图片验证码错误!");
        }

        //验证用户名
        if($postdata['username'])
        {
            $loginName = $postdata['username'];
            $data = userAuth::getAccountInfo($loginName);
            if($data)
            {
                $data['type'] = "forgot";
                $pagedata['data'] = $data;
                return view::make('topwap/passport/verify_vcode.html',$pagedata);
            }
        }

        $url = url::action('topwap_ctl_passport@goFindPwd');
        $msg = app::get('topwap')->_('账户不存在');
        return $this->splash('error',$url,$msg);
    }

    public function sendVcode()
    {
        $postdata = utils::_filter_input(input::get());
        $validator = validator::make(
            [$postdata['uname']],['required|mobile'],['您的手机号不能为空!|请输入正确的手机号码']
        );

        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            $url = url::action('topwap_ctl_passport@goFindPwd');
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0]);
            }
        }

        try {
            $this->passport->sendVcode($postdata['uname'],$postdata['type']);
        } catch(Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"验证码发送成功");
    }

    /**
     * @brief 找回密码第三步 验证手机验证码
     *
     * @return
     */
    public function verifyVcode()
    {
        $postdata = utils::_filter_input(input::get());
        $vcode = $postdata['vcode'];
        $loginName = $postdata['uname'];
        $sendType = $postdata['type'];
        try
        {
            $vcodeData=userVcode::verify($vcode,$loginName,$sendType);
            if(!$vcodeData)
            {
                throw new \LogicException('验证码输入错误');
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $userInfo = userAuth::getAccountInfo($loginName);
        $key = userVcode::getVcodeKey($loginName ,$sendType);
        $userInfo['key'] = md5($vcodeData['vcode'].$key.$userInfo['user_id']);

        if($sendType == "forgot")
        {
            $pagedata['data'] = $userInfo;
            $pagedata['account'] = $loginName;
            return view::make('topwap/passport/forgotten/setting_passport.html', $pagedata);
        }
        else
        {
            $pagedata['uname'] = $loginName;
            return view::make('topwap/passport/register/set_pwd.html',$pagedata);
        }
    }

    /**
     * @brief 找回密码第四部 设置新密码
     *
     * @return
     */
    public function settingPwd()
    {
        $postdata = utils::_filter_input(input::get());
        $userId = $postdata['userid'];
        $account = $postdata['account'];

        $vcodeData = userVcode::getVcode($account,'forgot');
        $key = userVcode::getVcodeKey($account,'forgot');

        if($account !=$vcodeData['account']  || $postdata['key'] != md5($vcodeData['vcode'].$key.$userId) )
        {
            $msg = app::get('topwap')->_('页面已过期,请重新找回密码');
            return $this->splash('failed',null,$msg,true);
        }

        $data['type'] = 'reset';
        $data['new_pwd'] = $postdata['password'];
        $data['user_id'] = $postdata['userid'];
        $data['confirm_pwd'] = $postdata['confirmpwd'];
        try
        {
            app::get('topwap')->rpcCall('user.pwd.update',$data,'buyer');

        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        $msg = "修改成功";
        $url = url::action('topwap_ctl_passport@goLogin');
        return $this->splash('success',$url,$msg,true);
    }

    public function logout()
    {
        userAuth::logout();
        return redirect::action('topwap_ctl_default@index');
    }


    private function __getFromUrl()
    {
        $url = utils::_filter_input(input::get('next_page', request::server('HTTP_REFERER')));
        $validator = validator::make([$url],['url'],['数据格式错误！']);
        if ($validator->fails())
        {
            return url::action('topwap_ctl_default@index');
        }
        if( !is_null($url) )
        {
            if( strpos($url, 'passport') )
            {
                return url::action('topwap_ctl_default@index');
            }
            return $url;
        }else{
            return url::action('topwap_ctl_default@index');
        }
    }

}

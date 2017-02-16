<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_passport extends topc_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->setLayoutFlag('passport');
        kernel::single('base_session')->start();

        $this->passport = kernel::single('topc_passport');
    }

    public function signin()
    {
        $pagedata['next_page'] = $this->__getFromUrl();
        $data = app::get('sysuser')->getConf('trustlogin_rule');

        // 获取信任登陆
        if (kernel::single('pam_trust_user')->enabled())
        {
            $trustInfoList = kernel::single('pam_trust_user')->getTrustInfoList('web', 'topc_ctl_trustlogin@callBack');
            $pagedata['trustInfoList'] = $trustInfoList;
        }

        $pagedata['isShowVcode'] = userAuth::isShowVcode('login');
        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('topc/passport/signin/signin.html',$pagedata);
    }

    public function signup()
    {
        //如果已登录则跳转到退出页
        if( userAuth::check() ) $this->logout();
        $pagedata['next_page'] = $this->__getFromUrl();
        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_user_license');
        return $this->page('topc/passport/signup/signup.html', $pagedata);
    }

    //登陆
    public function login()
    {
        $validator = validator::make(
            [input::get('account') , input::get('password')],
            ['required', 'required'],
            ['请输入账号!', '请输入密码!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        $verifycode = input::get('verifycode');
        if( userAuth::isShowVcode('login') )
        {
            if( !input::get('key') || empty($verifycode) || !base_vcode::verify(input::get('key'), $verifycode))
            {
                $msg = app::get('topc')->_('验证码填写错误') ;
                return $this->splash('error',$url,$msg,true);
            }
        }

        try
        {
            userAuth::setAttemptRemember(input::get('remember',null));

            if (userAuth::attempt(input::get('account'), input::get('password')))
            {
                $url = specialutils::filterCrlf(input::get('next_page'));
                kernel::single('topc_cart')->mergeCart();
                return $this->splash('success',$url,$msg);
            }
        }
        catch(Exception $e)
        {
            userAuth::setAttemptNumber();
            if( userAuth::isShowVcode('login') )
            {
                $url = url::action('topc_ctl_passport@signin');
            }
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
    }

    //注册
    public function create()
    {
        $data = utils::_filter_input(input::get());

        $codyKey = $data['key'];
        $verifycode = $data['verifycode'];
        $userInfo = $data['pam_user'];
        $vcode = $data['vcode'];
        //数据检测
        $validator = validator::make(
            ['loginAccount'=>$userInfo['account'],'license'=>input::get('license'),'password' => $userInfo['password'], 'password_confirmation' => $userInfo['pwd_confirm']],
            ['loginAccount'=>'required','license'=>'required','password' => 'min:6|max:20|confirmed','password_confirmation'=>'required'],
            ['loginAccount'=>'请输入用户名!','license'=>'请阅读并接受会员注册协议!','password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!','password_confirmation'=>'确认密码不能为空!']
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
            //$accountType = kernel::single('pam_tools')->checkLoginNameType($userInfo['account']);
            $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$userInfo['account']),'buyer');
            kernel::single('sysuser_passport')->checkSignupAccount($userInfo['account'],$accountType);
            if($accountType == "mobile")
            {
                $vcodeData=userVcode::verify($vcode,$userInfo['account'],'signup');

                if(!$vcodeData)
                {
                    throw new \LogicException(app::get('topc')->_('手机验证码错误'));
                }
            }
            else
            {
                if( empty($verifycode) || !base_vcode::verify($codyKey,$verifycode) )
                {
                    throw new \LogicException(app::get('topc')->_('验证码填写错误'));
                }
            }

            $userId = userAuth::signUp($userInfo['account'], $userInfo['password'], $userInfo['pwd_confirm']);
            userAuth::login($userId, $userInfo['account']);

            //登陆合并离线购物车
            kernel::single('topc_cart')->mergeCart();
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        // 跳成功页
        $url = url::action('topc_ctl_passport@signupSuccess', ['next_page' => $this->__getFromUrl()]);

        return $this->splash('success', $url, null, true);
    }

    public function signupSuccess()
    {
        $loginName = userAuth::getLoginName();
        $pagedata['loginname'] = $loginName;
        $pagedata['next_page'] = specialutils::filterCrlf(input::get('next_page'));
        $pagedata['sendPointNum'] = app::get('sysconf')->getConf('sendPoint.num');
        $pagedata['setting'] = app::get('sysconf')->getConf('open.sendPoint');

        return $this->page('topc/passport/signin/success.html', $pagedata);
     }

    //退出
    public function logout()
    {
        userAuth::logout();
        return redirect::action('topc_ctl_default@index');
    }

    //检查是否已经注册
    public function checkLoginAccount()
    {
        $signAccount = utils::_filter_input(input::get());
        $loginName = $signAccount['pam_user']['account'];
        $validator = validator::make(
            [$loginName],['required'],['请输入用户名!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        try
        {
            $data = userAuth::getAccountInfo($loginName);
            if($data)
            {
                throw new \LogicException('该用户名已被使用');
            }


            $json['needVerify'] = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$loginName),'buyer');

            kernel::single('sysuser_passport')->checkSignupAccount($loginName, $json['needVerify']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        return response::json($json);
    }

    //前端注册验证码的发送
    public function sendVcode()
    {
        $postData = utils::_filter_input(input::get());

        $validator = validator::make(
            [$postData['uname']],['required'],['您的邮箱或手机号不能为空!']
        );


        //验证码发送之前的判断
        //这里之前是判断用户post数据是否包含verifycode字段，如果不包含就跳过验证码了。这里改为判断用户使用手机注册（by Elrond at 2015.1.27）
        $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$postData['uname']),'buyer');
        if( $accountType == 'mobile' )
        {
            $valid = validator::make(
                [$postData['verifycode']],['required']
            );
            if($valid->fails())
            {
                return $this->splash('error',null,"图片验证码不能为空!");
            }
            if(!base_vcode::verify($postData['verifycodekey'],$postData['verifycode']))
            {
                return $this->splash('error',null,"图片验证码错误!");
            }

        }

       if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        //$accountType = kernel::single('pam_tools')->checkLoginNameType($postData['uname']);
        try
        {
            $this->passport->sendVcode($postData['uname'],$postData['type']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        if($accountType == "email")
        {
            return $this->splash('success',null,"邮箱验证链接已经发送至邮箱，请登录邮箱验证");
        }
        else
        {
            return $this->splash('success',null,"验证码发送成功");
        }
    }


    //找回密码第一步
    public function findPwd()
    {
        return $this->page('topc/passport/forgot/forgot.html');
    }

    //找回密码第二步
    public function findPwdTwo()
    {
        $postData = utils::_filter_input(input::get());
        if($postData)
        {
            $loginName = $postData['username'];
            $data = userAuth::getAccountInfo($loginName);

            if($data)
            {
                if( (!empty($data['email']) && $data['email_verify']) || !empty($data['mobile']))
                {
                    $send_status = 'true';
                }
                else
                {
                    $send_status = 'false';
                }
                $pagedata['send_status'] = $send_status;
                $pagedata['data'] = $data;
                return view::make('topc/passport/forgot/two.html', $pagedata);
            }
        }

        $url = url::action('topc_ctl_passport@findPwd');
        $msg = app::get('topc')->_('账户不存在');
        return $this->splash('error',$url,$msg);
    }

    //找回密码第三步
    public function findPwdThree()
    {
        $postData = utils::_filter_input(input::get());

        $vcode = $postData['vcode'];
        $loginName = $postData['uname'];
        $sendType = $postData['type'];
        $validator = validator::make(
            [$loginName],['required'],['用户不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        //$accountType = kernel::single('pam_tools')->checkLoginNameType($loginName);
        $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$loginName),'buyer');
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

        $pagedata['data'] = $userInfo;
        $pagedata['account'] = $loginName;
        if($accountType == "email")
        {
            return $this->page('topc/passport/forgot/email_three.html', $pagedata);
        }
        else
        {
            return view::make('topc/passport/forgot/three.html', $pagedata);
        }
    }
    //找回密码第四步
    public function findPwdFour()
    {
        $postData = utils::_filter_input(input::get());
        $userId = $postData['userid'];
        $account = $postData['account'];

        $vcodeData = userVcode::getVcode($account,'forgot');
        $key = userVcode::getVcodeKey($account,'forgot');

        if($account !=$vcodeData['account']  || $postData['key'] != md5($vcodeData['vcode'].$key.$userId) )
        {
            $msg = app::get('topc')->_('页面已过期,请重新找回密码');
            return $this->splash('failed',null,$msg,true);
        }

        $validator = validator::make(
            ['password' => $postData['password'] , 'password_confirmation' => $postData['confirmpwd']],
            ['password' => 'min:6|max:20|confirmed'],
            ['password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0],true);
            }
        }

        $data['type'] = 'reset';
        $data['new_pwd'] = $postData['password'];
        $data['user_id'] = $postData['userid'];
        $data['confirm_pwd'] = $postData['confirmpwd'];
        try
        {
            app::get('topc')->rpcCall('user.pwd.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topc_ctl_passport@findPwd');
            return $this->splash('error',$url,$msg,true);
        }
        return view::make('topc/passport/forgot/four.html');
    }

    private function __getFromUrl()
    {
        $url = specialutils::filterCrlf(input::get('next_page', request::server('HTTP_REFERER')));
        $validator = validator::make([$url],['url'],['数据格式错误！']);
        if ($validator->fails())
        {
            return url::action('topc_ctl_default@index');
        }
        if( !is_null($url) )
        {
            if( strpos($url, 'passport') )
            {
                return url::action('topc_ctl_default@index');
            }
            return $url;
        }else{
            return url::action('topc_ctl_default@index');
        }
    }
}


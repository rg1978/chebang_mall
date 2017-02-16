<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_passport extends topshop_controller {

    //简单的登录页面
    public function simpleSignin()
    {
        return view::make('topshop/passport/simpleSignin.html');
    }

    /**
     * @brief 显示登录页面
     */
    public function signin()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('企业账号登录');
        $this->set_tmpl('passport');
        if( pamAccount::isEnableVcode('sysshop') )
        {
            $pagedata['isShowVcode'] = 'true';
        }
        $pagedata['backgroundImgUrl'] = app::get('topshop')->res_url . '/images/bj_01.jpg';
        return $this->page('topshop/passport/signin.html',$pagedata);
    }

    /**
     * @brief 会员登录处理
     *
     * @return
     */
    public function login()
    {
        if( pamAccount::isEnableVcode('sysshop') )
        {
            // 验证图片验证码
            if(!base_vcode::verify(input::get('imagevcodekey'), input::get('imgcode')))
            {
                $msg = app::get('topshop')->_('图片验证码错误') ;
                $url = url::action('topshop_ctl_passport@signin');
                return $this->splash('error',$url,$msg,true);
            }
        }
        try
        {
            shopAuth::login(input::get('login_account'), input::get('login_password'));
        }
        catch(Exception $e)
        {
            $url = url::action('topshop_ctl_passport@signin');
            $msg = $e->getMessage();
        }
        if( pamAccount::check() )
        {
            if( input::get('remember_me') )
            {
                setcookie('LOGINNAME',trim(input::get('login_account')),time()+31536000,kernel::base_url().'/');
            }

            $url = url::action('topshop_ctl_index@index');
            $msg = app::get('topshop')->_('登录成功');
            $this->sellerlog('账号登录。账号名是'.input::get('login_account'));
            return $this->splash('success',$url,$msg,true);
        }
        else
        {
            return $this->splash('error',$url,$msg,true);
        }

    }

    /**
     * @brief 显示登录注册
     */
    public function signup()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('企业账号注册');
        $this->set_tmpl('pwdfind');
        $pagedata['license'] = app::get('sysshop')->getConf('sysshop.register.setting_sysshop_license');
        return $this->page('topshop/passport/signup.html', $pagedata);
    }

    public function isExists()
    {
        switch( input::get('type') )
        {
        case 'account':
            $str = input::get('login_account');
            break;
        case 'mobile':
            $str = input::get('mobile');
            break;
        case 'email':
            $str = input::get('email');
            break;
        }
        $flag = shopAuth::isExists($str, input::get('type'));
        $status =  $flag ? 'false' : 'true';
        return $this->isValidMsg($status);
    }

    /**
     * @brief 创建商家会员
     *
     * @return json
     */
    public function create()
    {
        if(input::get('license') != 'on')
        {
            $msg = $this->app->_('同意注册条款后才能注册');
            throw new \LogicException($msg);
        }

        try
        {
            $request = input::get();
            if(!$_SESSION['register']['mobile'])
                throw new LogicException('手机号码错误');
            $request['mobile'] = $_SESSION['register']['mobile'];
            $request['auth_type'] = 'AUTH_MOBILE';
            shopAuth::signupSeller($request);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }

        if( pamAccount::check() )
        {
            $url = url::action('topshop_ctl_index@index');
            $msg = app::get('topshop')->_('注册成功');
            return $this->splash('success',$url,$msg,true);
        }
        else
        {
            return $this->splash('error',null,$msg,true);
        }
    }

    public function logout()
    {
        pamAccount::logout();
        return redirect::action('topshop_ctl_passport@signin');
    }

    /**
     * @brief 会员密码修改
     */
    public function update()
    {
        return view::make('topshop/passport/update.html');
    }

    public function updatepwd()
    {
        try
        {
            shopAuth::modifyPwd(input::get());
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        $this->sellerlog('修改当前账号密码。');
        $url = url::action('topshop_ctl_passport@signin');
        $msg = app::get('topshop')->_('修改成功,请重新登陆');
        pamAccount::logout();

        return $this->splash('success',$url,$msg,true);
    }

}


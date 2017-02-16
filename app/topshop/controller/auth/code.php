<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_auth_code extends topshop_controller {
    protected $_tmpltype = 'auth_shop';

    public function send()
    {

        $request = input::get ();
        if (! $request) {return $this->splash ('error', null, '验证信息不能为空', true);}
        
        try {
            
            //验证图片验证码
            $validator = validator::make(
                    [$request['imgcode']],['required'],['图片验证码错误']
            );
            $validator->newFails();
            
            if(!base_vcode::verify($request['imagevcodekey'],$request['imgcode']))
            {
                return $this->splash('error',null,"图片验证码错误!", true);
            }
            
            $type = $request ['type'];
            
            // 查看验证类型
            switch ($type) {
                case 'mobile' :
                    $mobile = $request ['auth_info'];
                    //验证手机号
                    $validator = validator::make(
                            ['auth_mobile' => $mobile],
                            ['auth_mobile' => 'required|mobile'],
                            ['auth_mobile' => '手机号必填|手机格式不正确']
                            );
                    $validator->newFails();
                    
                    // 验证所填写的手机号是否已存在
                    $bool = $this->isErrorInfo($type, $mobile, $request['ac'], false);
                    if($bool)
                    {
                        throw new \LogicException (app::get ('topshop')->_ ('手机号已存在，请换一个重试'));
                    }
                    
                    userVcode::send_sms ($this->_tmpltype, $mobile);
                    break;
                case 'email' :
                    $email = $request ['auth_info'];
                    //验证手机号
                    $validator = validator::make(
                            ['auth_email' => $email],
                            ['auth_email' => 'required|email'],
                            ['auth_email' => '邮箱必填|邮箱格式不正确']
                    );
                    $validator->newFails();
                    
                    // 验证所填写的邮箱是否已存在
                    $bool = $this->isErrorInfo($type, $email, $request['ac'], false);
                    if($bool)
                    {
                        throw new \LogicException (app::get ('topshop')->_ ('邮箱地址已存在，请换一个重试'));
                    }
                    
                    $contentUrl = '';
                    userVcode::send_email ($this->_tmpltype, $email, $contentUrl, false);
                    break;
            }
        } catch ( Exception $e ) {
            $msg = $e->getMessage ();
            return $this->splash ('error', null, $msg, true);
        }
        
        $msg = '验证码发送成功，请注意查收';
        return $this->splash ('success', null, $msg, true);
    }

    /**
     * 认证
     * */
    public function checkAuth($isAuth = TRUE)
    {

        $request = input::get ();
        // 查看认证类型
        $type = $request ['type'];
        
        try {
            //验证验证码
            $validator = validator::make(
                    ['auth_code' => $request ['auth_code']],
                    ['auth_code' => 'required|numeric'],
                    ['auth_code' => '请填写收到的验证码|验证码错误']
            );
            $validator->newFails();
            // 查看验证类型
            switch ($type) {
                case 'mobile' :
                    $mobile = $request ['auth_mobile'];
                    
                    //验证表单数据
                    $validator = validator::make(
                            ['auth_mobile' => $mobile],
                            ['auth_mobile' => 'required|mobile'],
                            ['auth_mobile' => '手机号必填|手机格式不正确']
                    );
                    $validator->newFails();
                    if (! userVcode::verify ($request ['auth_code'], $mobile, $this->_tmpltype)) {
                        throw new \LogicException(app::get('topshop')->_('验证码错误，请重新填写'));
                    }
                  
                    // 判断认证状态
                    if($isAuth)
                    {
                        $data ['auth_type'] = $this->__getAuthType ($type);
                    }
                    
                    $data['mobile'] = $mobile;
                    $msg = '手机验证成功！';
                    break;
                case 'email' :
                    $email = $request ['auth_email'];
                    if (! userVcode::verify ($request ['auth_code'], $email, $this->_tmpltype)) {
                        throw new \LogicException(app::get('topshop')->_('验证码错误，请重新填写'));
                    }
                   
                    // 判断认证状态
                    if($isAuth)
                    {
                        $data ['auth_type'] = $this->__getAuthType ($type);
                    }
                    
                    $data['email'] = $email;
                    $msg = '邮箱验证成功！';
                    break;
            }
            //调用接口修改认证状态
            $data['shop_id'] = $this->shopId;
            $data ['seller_id'] = $this->sellerId;
            app::get('topshop')->rpcCall('auth.shop.updata',$data);
            
        } catch ( Exception $e ) {
            $msg = $e->getMessage ();
            return $this->splash ('error', null, $msg, true);
        }
        
        $url = url::action('topshop_ctl_auth_index@index');
        
        return $this->splash('success', $url, $msg, true);
    }

    /**
     * 修改认证信息
     * 
     * */
    public function updateAuth()
    {
        // 判断是否验证过登录密码，若未验证则跳转到安全中心首页
        if( ! cache::store('misc')->has('auth_check_info'))
        {return redirect::action ('topshop_ctl_auth_index@index');}
        
        $checkData = unserialize(cache::store('misc')->pull('auth_check_info'));
        if (! isset ($checkData ['hash_str']) || ! hash::check ($checkData['auth_str'], $checkData ['hash_str']))
        {return redirect::action ('topshop_ctl_auth_index@index');}
        
        $this->contentHeaderTitle = app::get ('topshop')->_ ('安全中心');
        $request = input::get();
        return $this->page('topshop/auth/updateauth.html', $request);
    }
    
    public function updateAuthCheck()
    {
        return $this->checkAuth(false);
    }
    
    /**
     * 获取认证状态
     * @param string $type
     * @return string 
     * */
    private function __getAuthType($type)
    {

        $sellinfo = shopAuth::getSellerData ();
        $authType = $sellinfo ['auth_type'];
        switch ($authType) {
            case 'UNAUTH' :
                if ($type == 'mobile') {
                    $authType = 'AUTH_MOBILE';
                }
                if ($type == 'email') {
                    $authType = 'AUTH_EMAIL';
                }
                break;
            case 'AUTH_MOBILE' :
                if ($type == 'email') {
                    $authType = 'AUTH_ALL';
                }
                break;
            case 'AUTH_EMAIL' :
                if ($type == 'mobile') {
                    $authType = 'AUTH_ALL';
                }
                break;
            case 'AUTH_ALL' :
                return $authType;
                break;
        }
        
        return $authType;
    }

    /**
     *  判断手机或邮箱是否出现重复
     *  
     *  @param string $type
     *  @param string $str
     *  @param string $ac
     *  @param boolean $isJson 
     * */
    public function isErrorInfo($type = null, $str = null, $ac = null, $isJson = true)
    {
        if($type == null)
        {
            $type = input::get('type');
        }
        
        switch( $type )
        {
            case 'mobile':
                if($str == null)
                {
                    $str = input::get('auth_mobile');
                }
                
                break;
            case 'email':
                if($str == null)
                {
                    $str = input::get('auth_email');
                }
        }
        
        if($ac == null)
        {
            $ac = input::get('ac');
        }
        $flag = shopAuth::isAuthExits($str, $type, $ac);
        if(! $isJson)
        {
            return $flag;
        }
        $status =  $flag ? 'false' : 'true';
        return $this->isValidMsg($status);
    }
}
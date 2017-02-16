<?php

/**
 * finde.php 找回密码
 *
 * @author Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_find extends topshop_controller {
    
    protected $_tmpltype = 'findPw_shop';

    public function index()
    {
        $this->contentHeaderTitle = app::get ('topshop')->_ ('企业账号密码找回');
        $this->set_tmpl ('pwdfind');
        return $this->page ('topshop/find/find_index.html');
    }
    
    // 找回密码第一步
    public function firstStep()
    {

        $type = input::get('type', 'mobile');
        $this->contentHeaderTitle = app::get ('topshop')->_ ('企业账号密码找回');
        $this->set_tmpl ('pwdfind');
        return $this->page ('topshop/find/find_one.html', array (
                'type' => $type 
        ));
    }
    // 找回密码第二步
    public function secondStep()
    {
        if(! cache::store('session')->has('find_auth_info'))
        {
            return redirect::action ('topshop_ctl_find@index');
        }
        $data = unserialize(cache::store('session')->pull('find_auth_info'));
        // 如果未经过第一步验证,则跳转到第一步
        if (! isset ($data ['verified']) || ! hash::check ($data['auth_str'], $data ['verified']))
        {return redirect::action ('topshop_ctl_find@index');}
        
        unset($data['verified'], $data['auth_str']);
        
        $this->contentHeaderTitle = app::get ('topshop')->_ ('企业账号密码找回');
        $this->set_tmpl ('pwdfind');
        
        return $this->page ('topshop/find/find_two.html', $data);
    }
    
    // 重置密码
    public function resetPassword()
    {

        $request = input::get ();
        try
        {
            $msg = '密码重置失败';
            if (! isset ($request ['type']))
            {throw new \LogicException (app::get ('topshop')->_ ($msg));}
            // 获取商家账户序号ID
            $type = $request ['type'];
            $filter = array ();
            
            switch ($type)
            {
                case 'mobile' :
                    if (! isset ($request ['mobile']))
                    {throw new \LogicException (app::get ('topshop')->_ ($msg));}
                    if (! $this->isAuth ($request ['mobile'], $type, false))
                    {throw new \LogicException (app::get ('topshop')->_ ($msg));}
                    $filter ['mobile'] = $request ['mobile'];
                    break;
                
                case 'email' :
                    if (! isset ($request ['email']))
                    {throw new \LogicException (app::get ('topshop')->_ ($msg));}
                    if (! $this->isAuth ($request ['email'], $type, false))
                    {throw new \LogicException (app::get ('topshop')->_ ($msg));}
                    $filter ['email'] = $request ['email'];
                    break;
                
                default :
                    throw new \LogicException (app::get ('topshop')->_ ($msg));
                    break;
            }
            
            $filter ['seller_type'] = '0';
            $sellerInfo = shopAuth::getFindAuthInfo ($filter);
            if (! $sellerInfo)
            {throw new \LogicException (app::get ('topshop')->_ ($msg));}
            
            // 开始修改密码
            $sellerId = $sellerInfo ['seller_id'];
            $data = array ();
            $data ['login_password'] = $request ['login_password'];
            $data ['psw_confirm'] = $request ['psw_confirm'];
            shopAuth::resetPwd ($sellerId, $data);
        } catch ( Exception $e )
        {
            // 返回第一步重新验证身份
            $msg = $e->getMessage ();
            return $this->splash ('error', null, $msg, true);
        }
        
        $url = url::action ('topshop_ctl_passport@signin');
        $msg = '密码重置成功';
        return $this->splash ('success', $url, $msg, true);
    }
    
    // 验证信息
    public function checkFindInfo()
    {
        $request = input::get ();
        // 查看认证类型
        $type = $request ['type'];
        
        try
        {
            // 验证验证码
            $validator = validator::make ([ 
                    'find_code' => $request ['find_code'] 
            ], [ 
                    'find_code' => 'required|numeric' 
            ], [ 
                    'find_code' => '请填写收到的验证码|验证码错误' 
            ]);
            $validator->newFails ();
            // 查看验证类型
            switch ($type)
            {
                case 'mobile' :
                    $mobile = $request ['mobile'];
                    
                    // 验证表单数据
                    $validator = validator::make ([ 
                            'mobile' => $mobile 
                    ], [ 
                            'mobile' => 'required|mobile' 
                    ], [ 
                            'mobile' => '手机号必填|手机格式不正确' 
                    ]);
                    $validator->newFails ();
                    // 查看手机是否认证
                    if (! $this->isAuth ($mobile, $type, false))
                    {throw new \LogicException (app::get ('topshop')->_ ('手机号码未认证，请换一个重试'));}
                    // 验证手机和验证码
                    if (! userVcode::verify ($request ['find_code'], $mobile, $this->_tmpltype))
                    {throw new \LogicException (app::get ('topshop')->_ ('验证码错误，请重新填写'));}
                    
                    $data ['mobile'] = $mobile;
                    break;
                case 'email' :
                    $email = $request ['email'];
                    // 验证表单数据
                    $validator = validator::make ([ 
                            'email' => $email 
                    ], [ 
                            'email' => 'required|email' 
                    ], [ 
                            'email' => '邮箱必填|邮箱格式不正确' 
                    ]);
                    $validator->newFails ();
                    // 查看邮箱是否认证
                    if (! $this->isAuth ($email, $type, false))
                    {throw new \LogicException (app::get ('topshop')->_ ('邮箱未认证，请换一个重试'));}
                    // 验证邮箱和验证码
                    if (! userVcode::verify ($request ['find_code'], $email, $this->_tmpltype))
                    {throw new \LogicException (app::get ('topshop')->_ ('验证码错误，请重新填写'));}
                    
                    $data ['email'] = $email;
                    break;
            }
        } catch ( Exception $e )
        {
            $msg = $e->getMessage ();
            return $this->splash ('error', null, $msg, true);
        }
        
        // 跳转
        $msg = '验证成功';
        // 处理验证数据
        $data ['type'] = $type;
        $parameters = array ();
        $parameters = $data;
        // 生成一个经过加密的验证标识
        $authStr = str_random();
        $hashStr = hash::make ($authStr);
        // 这个参数表示已经过第一步验证
        $parameters ['verified'] = $hashStr;
        $parameters['auth_str'] = $authStr;
        // 设置缓存, 有效时间为一个小时
        cache::store('session')->add('find_auth_info', serialize($parameters), 60);
        
        $url = url::action ('topshop_ctl_find@secondStep');
        
        return $this->splash ('success', $url, $msg, true);
    }
    
    /**
     * 判断手机或邮箱是否存在
     * @param string 手机或者邮箱
     * @param string 验证状态
     * @param bool $isJson 是否返回json
     **/
    public function isAuth($str = null, $type = null, $isJson = true)
    {

        $flag = false;
        
        // 处理条件
        $params = array ();
        $params ['seller_type'] = '0';
        if (is_null ($type))
        {
            $type = input::get ('type');
        }
        
        switch ($type)
        {
            case 'mobile' :
                if (is_null ($str))
                {
                    $str = input::get ('mobile');
                }
                
                // 判断手机号是否正确，在ajax请求的时候出现错误时返回字符串true，内部调用返回布尔类型false
                $validator = validator::make ([ 
                        'mobile' => $str 
                ], [ 
                        'mobile' => 'required|mobile' 
                ], [ 
                        'mobile' => '手机号必填|手机格式不正确' 
                ]);
                if ($validator->fails ())
                {
                    if (! $isJson)
                    {return $flag;}
                    return $this->isValidMsg ('true');
                }
                
                $params ['mobile'] = $str;
                break;
            case 'email' :
                if (is_null ($str))
                {
                    $str = input::get ('email');
                }
                
                // 判断邮箱是否正确，在ajax请求的时候出现错误时返回字符串true，内部调用返回布尔类型false
                $validator = validator::make ([
                        'email' => $str
                ], [
                        'email' => 'required|email'
                ], [
                        'email' => '邮箱必填|格式不正确'
                ]);
                if ($validator->fails ())
                {
                    if (! $isJson)
                    {return $flag;}
                    return $this->isValidMsg ('true');
                }
                
                $params ['email'] = $str;
                break;
        }
        
        // 获取认证状态
        $authInfo = shopAuth::getFindAuthInfo ($params);
        $flag = $this->__proAuthInfo ($authInfo, $type);
        if (! $isJson)
        {return $flag;}
        $status = $flag ? 'true' : 'false';
        return $this->isValidMsg ($status);
    }
    
    // 发送验证码
    public function send()
    {

        $request = input::get ();
        if (! $request)
        {return $this->splash ('error', null, '验证信息不能为空', true);}
        
        try
        {
            $type = $request ['type'];
            // 查看验证类型
            switch ($type)
            {
                case 'mobile' :
                    $this->__checkImageCode($request);
                    $mobile = $request ['auth_info'];
                    // 验证手机号
                    $validator = validator::make ([ 
                            'auth_mobile' => $mobile 
                    ], [ 
                            'auth_mobile' => 'required|mobile' 
                    ], [ 
                            'auth_mobile' => '手机号必填|手机格式不正确' 
                    ]);
                    $validator->newFails ();
                    
                    // 查看手机号是否认证
                    if (! $this->isAuth ($mobile, $type, false))
                    {throw new \LogicException (app::get ('topshop')->_ ('手机号码未认证，请换一个重试'));}
                    
                    userVcode::send_sms ($this->_tmpltype, $mobile);
                    break;
                case 'email' :
                    $email = $request ['auth_info'];
                    // 验证邮箱
                    $validator = validator::make ([ 
                            'auth_email' => $email 
                    ], [ 
                            'auth_email' => 'required|email' 
                    ], [ 
                            'auth_email' => '邮箱必填|邮箱格式不正确' 
                    ]);
                    $validator->newFails ();
                    
                    // 查看邮箱是否认证
                    if (! $this->isAuth ($email, $type, false))
                    {throw new \LogicException (app::get ('topshop')->_ ('邮箱未认证，请换一个重试'));}
                    
                    $contentUrl = '';
                    userVcode::send_email ($this->_tmpltype, $email, $contentUrl, false);
                    break;
            }
        } catch ( Exception $e )
        {
            $msg = $e->getMessage ();
            return $this->splash ('error', null, $msg, true);
        }
        
        $msg = '验证码发送成功，请注意查收';
        return $this->splash ('success', null, $msg, true);
    }

    /**
     * 处理验证数据
     * 
     * @param array $authInfo
     * @param string $type
     * @return bool
     * */
    private function __proAuthInfo($authInfo, $type)
    {

        if (! $authInfo)
        {return false;}
        
        if ($authInfo ['auth_type'] == 'UNAUTH')
        {return false;}
        
        $flag = false;
        switch ($type)
        {
            
            case 'mobile' :
                if ($authInfo ['auth_type'] == 'AUTH_MOBILE' || $authInfo ['auth_type'] == 'AUTH_ALL')
                {
                    $flag = true;
                }
                break;
            
            case 'email' :
                if ($authInfo ['auth_type'] == 'AUTH_EMAIL' || $authInfo ['auth_type'] == 'AUTH_ALL')
                {
                    $flag = true;
                }
                break;
        }
        
        return $flag;
    }
    
    // 验证图形验证码
    private function __checkImageCode($request)
    {
        $validator = validator::make ([
                $request ['imgcode']
        ], [
                'required'
        ], [
                '图片验证码错误'
        ]);
        $validator->newFails ();
        
        if (! base_vcode::verify ($request ['imagevcodekey'], $request ['imgcode']))
        {
            throw new \LogicException (app::get ('topshop')->_ ('图片验证码错误!'));
        }
        
        return true;
    }
}

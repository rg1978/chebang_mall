<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_auth_index extends topshop_controller {

    public function index()
    {
        // 获取当前用户数据
        $sellData = shopAuth::getSellerData ();

        // 处理数据
        $this->__replaceStart ($sellData ['mobile'], $sellData ['email']);
        $this->contentHeaderTitle = app::get ('topshop')->_ ('安全中心');

        return $this->page ('topshop/auth/safe.html', $sellData);
    }

    /**
     * 验证登录密码
     * */
    public function checkPassword()
    {
        $pagedata = input::get ();
        $pagedata['type'] = ($pagedata['type'] == 'mobile') ? 'mobile' : 'email';
        $this->contentHeaderTitle = app::get ('topshop')->_ ('安全中心');

        return $this->page ('topshop/auth/pwd.html', $pagedata);
    }

    public function doCheckPassword()
    {

        try {
            $data = input::get();
            $data['type'] = ($data['type'] == 'mobile') ? 'mobile' : 'email';
            shopAuth::checkPassword($data);
        } catch ( Exception $e ) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }

        if(input::get('ac') == 'update')
        {
            $url = url::action('topshop_ctl_auth_code@updateAuth', array (
                    'type' => $data['type']
            ));
        }else
        {
            $url = url::action('topshop_ctl_auth_index@auth', array (
                    'type' => $data['type']
            ));
        }

        // 把验证状态放到缓存中
        // 生成随机字符串
        $authArr = array();
        $authArr['auth_str'] = str_random();
        // 对随机字符串进行加密
        $authArr['hash_str'] = hash::make($authArr['auth_str']);
        // 把验证数据存放到缓存中
        cache::store('misc')->add('auth_check_info', serialize($authArr), 60);

        $msg = app::get ('topshop')->_ ('密码验证成功');

        return $this->splash ('success', $url, $msg, true);
    }

    // 验证页
    public function auth()
    {
        // 判断是否验证过登录密码，若未验证则跳转到安全中心首页
        if( ! cache::store('misc')->has('auth_check_info'))
        {return redirect::action ('topshop_ctl_auth_index@index');}

        $checkData = unserialize(cache::store('misc')->pull('auth_check_info'));
        if (! isset ($checkData ['hash_str']) || ! hash::check ($checkData['auth_str'], $checkData ['hash_str']))
        {return redirect::action ('topshop_ctl_auth_index@index');}

        $type = input::get('type');
        $pagedata['type'] = $type;
        $this->contentHeaderTitle = app::get ('topshop')->_ ('安全中心');

        return $this->page('topshop/auth/authinfo.html', $pagedata);
    }
    /**
     * 处理手机号和邮箱
     * @param string $mobile
     * @param string $email
     * @return bool
     * */
    private function __replaceStart(&$mobile, &$email)
    {

        if (! $email || ! $mobile) {return false;}

        // 替换手机号
        $mobile = substr_replace ($mobile, '****', 3, 4);
        // 替换邮箱
        $pos = strpos ($email, '@');
        if (! $pos) {return false;}

        $pos = $pos - 2;

        if ($pos < 0) {return false;}

        $email = substr_replace ($email, str_repeat ('*', $pos), 1, $pos);
        return true;
    }

}

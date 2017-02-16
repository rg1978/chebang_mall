<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
// 页面显示 登陆注册验证码

class base_vcode
{
    function __construct(){
        $this->obj = kernel::single('base_vcode_gd');
        kernel::single('base_session')->start();
    }

    function length($len) {
        $this->obj->length($len);
        return true;
    }

    public function setPicSize($height=35, $width=100)
    {
        $this->obj->setPicSize($height, $width);
        return true;
    }

    function verify_key($key)
    {
        $sess_id = kernel::single('base_session')->sess_id();
        $key = 'VCODE_VERIFY:'.$key.$sess_id;
        $minutes = 3;

        cache::store('vcode')->put($key, $this->obj->get_code(), $minutes);
    }

    static function verify($key,$value)
    {
        kernel::single('base_session')->start();
        $value = strtolower($value);
        $sess_id = kernel::single('base_session')->sess_id();
        $vcodekey = 'VCODE_VERIFY:'.$key.$sess_id;

        $vcode = cache::store('vcode')->get($vcodekey);
        //使用后则是过期
        cache::store('vcode')->forget($vcodekey);

        if ($value && ($vcode == strval($value)))
        {
            return true;
        }

        return false;
    }

    function display(){
        $this->obj->display();
    }

    function base64Image()
    {
        return $this->obj->base64Image();
    }
}

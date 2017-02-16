<?php
/**
 * 登录登出商品收藏，店铺收藏数据cookie存储
 */
class pam_events_listeners_collect {

    public function login($userId)
    {
        $collectData = app::get('topc')->rpcCall('user.collect.info',array('user_id'=>$userId));
        setcookie('collect',serialize($collectData));
    }

    public function logout()
    {
        $userId = userAuth::id();
        setcookie('collect','');
    }
}


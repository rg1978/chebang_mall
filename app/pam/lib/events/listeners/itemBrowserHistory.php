<?php
/**
 * 浏览商品
 */
class pam_events_listeners_itemBrowserHistory {

    public function handle($userId)
    {
        $itemId = $_COOKIE['itemBrowserHistory'];

        if( $itemId)
        {
            app::get('pam')->rpcCall('user.browserHistory.set', ['user_id'=>$userId, 'itemIds'=>$itemId]);

            $path = $path ?: kernel::base_url().'/';
            $life = 315360000;
            $expire = $expire === false ? time() + $life : $expire;
            setcookie('itemBrowserHistory','', $expire, $path);
        }
        return true;
    }
}


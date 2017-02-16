<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class sysuser_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('sysuser')->model('account')->delete( array('*') );
        app::get('sysuser')->model('shop_fav')->delete( array('*') );
        app::get('sysuser')->model('trustinfo')->delete( array('*') );
        app::get('sysuser')->model('user')->delete( array('*') );
        app::get('sysuser')->model('user_addrs')->delete( array('*') );
        app::get('sysuser')->model('user_coupon')->delete( array('*') );
        app::get('sysuser')->model('user_deposit')->delete( array('*') );
        app::get('sysuser')->model('user_deposit_log')->delete( array('*') );
        app::get('sysuser')->model('user_experience')->delete( array('*') );
        app::get('sysuser')->model('user_fav')->delete( array('*') );
        app::get('sysuser')->model('user_item_notify')->delete( array('*') );
        app::get('sysuser')->model('user_point')->delete( array('*') );
        app::get('sysuser')->model('user_pointlog')->delete( array('*') );
        app::get('sysuser')->model('user_points')->delete( array('*') );
        app::get('sysuser')->model('user_trade_count')->delete( array('*') );
    }
    #End Func
}
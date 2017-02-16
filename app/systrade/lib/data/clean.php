<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class systrade_data_clean
{

    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('systrade')->model('activity_detail')->delete( array('*') );
        app::get('systrade')->model('cart')->delete( array('*') );
        app::get('systrade')->model('cart_coupon')->delete( array('*') );
        app::get('systrade')->model('cart_item')->delete( array('*') );
        app::get('systrade')->model('log')->delete( array('*') );
        app::get('systrade')->model('order')->delete( array('*') );
        app::get('systrade')->model('order_complaints')->delete( array('*') );
        app::get('systrade')->model('promotion_detail')->delete( array('*') );
        app::get('systrade')->model('trade')->delete( array('*') );
        app::get('systrade')->model('trade_cancel')->delete( array('*') );
    }
    #End Func
}

<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class sysshop_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('sysshop')->model('account')->delete( array('*') );
        app::get('sysshop')->model('enterapply')->delete( array('*') );
        app::get('sysshop')->model('roles')->delete( array('*') );
        app::get('sysshop')->model('seller')->delete( array('*') );
        app::get('sysshop')->model('shop')->delete( array('*') );
        app::get('sysshop')->model('shop_cat')->delete( array('*') );
        app::get('sysshop')->model('shop_info')->delete( array('*') );
        app::get('sysshop')->model('shop_notice')->delete( array('*') );
        app::get('sysshop')->model('shop_rel_brand')->delete( array('*') );
        app::get('sysshop')->model('shop_rel_lv1cat')->delete( array('*') );
        app::get('sysshop')->model('shop_rel_seller')->delete( array('*') );
    }
    #End Func
}
<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class syslogistics_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('syslogistics')->model('delivery')->delete( array('*') );
        app::get('syslogistics')->model('delivery_detail')->delete( array('*') );
        app::get('syslogistics')->model('dlycorp')->delete( array('*') );
        app::get('syslogistics')->model('dlytmpl')->delete( array('*') );
        app::get('syslogistics')->model('ziti')->delete( array('*') );

    }
    #End Func
}
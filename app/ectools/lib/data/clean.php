<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class ectools_data_clean
{

    /*
     * 清除初始化数据
     */
    public function clean()
    {
        //app::get('ectools')->model('analysis')->delete( array('*') );
        //app::get('ectools')->model('analysis_logs')->delete( array('*') );
        app::get('ectools')->model('payments')->delete( array('*') );
        app::get('ectools')->model('refunds')->delete( array('*') );
        app::get('ectools')->model('trade_paybill')->delete( array('*') );
    }
    #End Func
}
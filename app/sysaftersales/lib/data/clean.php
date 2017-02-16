<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class sysaftersales_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('sysaftersales')->model('aftersales')->delete( array('*') );
        app::get('sysaftersales')->model('refunds')->delete( array('*') );
    }
    #End Func
}
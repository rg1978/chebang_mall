<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class sysitem_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('sysitem')->model('item')->delete( array('*') );
        app::get('sysitem')->model('item_count')->delete( array('*') );
        app::get('sysitem')->model('item_desc')->delete( array('*') );
        app::get('sysitem')->model('item_nature_props')->delete( array('*') );
        app::get('sysitem')->model('item_promotion')->delete( array('*') );
        app::get('sysitem')->model('item_status')->delete( array('*') );
        app::get('sysitem')->model('item_store')->delete( array('*') );
        app::get('sysitem')->model('sku')->delete( array('*') );
        app::get('sysitem')->model('sku_store')->delete( array('*') );
        app::get('sysitem')->model('spec_index')->delete( array('*') );
    }
    #End Func
}
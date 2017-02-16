<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class syscategory_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('syscategory')->model('brand')->delete( array('*') );
        app::get('syscategory')->model('cat')->delete( array('*') );
        kernel::single('syscategory_data_cat')->cleanCatsCache();
        app::get('syscategory')->model('cat_rel_brand')->delete( array('*') );
        app::get('syscategory')->model('cat_rel_prop')->delete( array('*') );
        app::get('syscategory')->model('prop_values')->delete( array('prop_id|noequal'=>'1') );
        app::get('syscategory')->model('props')->delete( array('prop_id|noequal'=>'1') );
    }
    #End Func
}
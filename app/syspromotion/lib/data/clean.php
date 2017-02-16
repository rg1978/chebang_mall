<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class syspromotion_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('syspromotion')->model('activity')->delete( array('*') );
        app::get('syspromotion')->model('activity_item')->delete( array('*') );
        app::get('syspromotion')->model('activity_register')->delete( array('*') );
        app::get('syspromotion')->model('coupon')->delete( array('*') );
        app::get('syspromotion')->model('coupon_item')->delete( array('*') );
        app::get('syspromotion')->model('freepostage')->delete( array('*') );
        app::get('syspromotion')->model('freepostage_item')->delete( array('*') );
        app::get('syspromotion')->model('fulldiscount')->delete( array('*') );
        app::get('syspromotion')->model('fulldiscount_item')->delete( array('*') );
        app::get('syspromotion')->model('fullminus')->delete( array('*') );
        app::get('syspromotion')->model('fullminus_item')->delete( array('*') );
        app::get('syspromotion')->model('promotions')->delete( array('*') );
        app::get('syspromotion')->model('xydiscount')->delete( array('*') );
        app::get('syspromotion')->model('xydiscount_item')->delete( array('*') );
    }
    #End Func
}
<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class site_theme_helper
{

    public function __construct()
    {
        $this->themesdir = array('wap'=>WAP_THEME_DIR, 'pc'=>THEME_DIR);
    }

    function function_header($params){
        $service = kernel::service("site_theme_helper.".$params['app']);
        if(method_exists($service, 'function_header'))
        {
            $ret = $service->function_header($params);
        }
        return $ret;
    }

    function function_footer($params){
        $service = kernel::service("site_theme_helper.".$params['app']);
        if(method_exists($service, 'function_footer'))
        {
            $ret = $service->function_footer($params);
        }
        return $ret;
    }

}//End Class

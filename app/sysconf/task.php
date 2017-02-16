<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysconf_task
{
    function post_update($params){
        if($dbver['dbver'] < '0.5'){
            if($name = app::get('sysconf')->getConf('site.name'))
            {
                app::get('site')->setConf('site.name',$name);
            }
            if($logo = app::get('sysconf')->getConf('site.logo'))
            {
                app::get('site')->setConf('site.logo',$logo);
            }
            if($loginlogo = app::get('sysconf')->getConf('site.loginlogo'))
            {
                app::get('site')->setConf('site.loginlogo',$loginlogo);
            }
        }
    }
}//End Class

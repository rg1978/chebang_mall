<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_syscache_tbdefine extends base_syscache_abstract implements base_interface_syscache_farmer {
    
    public function get_data()
    {
        $apps = app::get('base')->database()->executeQuery('select app_id from base_apps where status = "active"')->fetchAll();
        $apps = array_column($apps, 'app_id');

        foreach($apps as $app)
        {
            $dbtable = new base_application_dbtable;
            foreach ($dbtable->detect($app) as $name=>$item) {
                $tbdefines[$item->real_table_name()] = $item->realLoad();
            }
        }
        return $tbdefines;
    }
}


<?php
/**
 * cleardeltadata.php 
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class search_tasks_cleardeltadata extends base_task_abstract implements base_interface_task{
    
    public function exec($params=null)
    {
        $filter = array();
        $filter['last_modify|lthan'] = time()-86400;
        $filter['index_name'] = 'sysitem_item';
        
        $objMdl = app::get('search')->model('delta');
        
        return $objMdl->delete($filter);
    }
}
 
 
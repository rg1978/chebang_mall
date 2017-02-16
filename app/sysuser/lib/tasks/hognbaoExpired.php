<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysuser_tasks_hognbaoExpired extends base_task_abstract implements base_interface_task{

    public function exec($params=null)
    {
        $objMdlHongbao = app::get('sysuser')->model('user_hongbao');

        return $objMdlHongbao->update(['status'=>'expired'], array('end_time|sthan'=>time()));
    }
}

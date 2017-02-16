<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class syspromotion_tasks_hongbao extends base_task_abstract implements base_interface_task{

    public function exec($params=null)
    {
        $objMdlHongbao = app::get('syspromotion')->model('hongbao');

        return $objMdlHongbao->update(['status'=>'stop'], array('get_end_time|sthan'=>time()));
    }
}

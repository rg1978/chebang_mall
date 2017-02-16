<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topdev_ctl_index extends topdev_controller {

    public function index()
    {
        //面包屑
        $this->runtimePath = array(
            ['title' => app::get('topdev')->_('桌面')],
        );
        $this->activeMenu = '桌面';
        return $this->page('topdev/index.html');
    }
}

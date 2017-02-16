<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_default extends topc_controller
{
    public function index()
    {
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('首页'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('index');

        return $this->page();
    }

    public function redirect()
    {
        return view::make('topc/redirect.html', $pagedata);
    }
}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topm_ctl_default extends topm_controller
{
    public function index()
    {
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topm')->_('首页〉'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('index');
        return $this->page();
    }

    public function switchToPc()
    {
        setcookie('browse', 'pc');
        return redirect::route('topc');
    }
}

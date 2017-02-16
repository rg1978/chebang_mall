<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topwap_ctl_default extends topwap_controller
{
    public function index()
    {
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topwap')->_('首页〉'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('index');
        return $this->page("topwap/index.html");
    }

    public function switchToPc()
    {
        setcookie('browse', 'pc');
        return redirect::route('topc');
    }
}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_site_view_helper {

    public function function_header($params, $template, $pagedata)
    {
        return view::make('topc/common/header.html', $pagedata)->render();
    }

    public function function_footer($params, $template)
    {
        $html = app::get('site')->getConf('system.foot_edit');
        return $html;
    }

}//结束


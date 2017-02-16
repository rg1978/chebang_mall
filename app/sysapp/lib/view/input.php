<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysapp_view_input{

    function input_applink($params){

        $pagedata['domid'] = view::ui()->new_dom_id();
        $pagedata['value'] = $params['value'];
        $pagedata['name'] = $params['name'];
        $pagedata['linktypename'] = $params['linktypename'];
        $pagedata['linktypevalue'] = $params['linktypevalue'];

        return view::make('sysapp/ui/applink.html', $pagedata)->render();
    }

}//End Class

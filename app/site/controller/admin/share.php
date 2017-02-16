<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class site_ctl_admin_share extends site_admin_controller {

    /*
     * workground
     * @var string
     */
    var $workground = 'site_ctl_admin_share';

    public function index() {
        $all_settings = array(
            app::get('site')->_('微信分享配置') => array (
                'site.appId',
                'site.appSecret',
            ),
        );
        $html = kernel::single ( 'site_base_setting', $this->app )->process ( $all_settings );
        $pagedata ['_PAGE_CONTENT'] = $html;
        return $this->page('desktop/common/default.html', $pagedata);
    } //End Function

}//End Class

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysim_ctl_config extends desktop_controller
{

    private $plugins = array(
        'toputil_im_plugin_webcall'  => '365WebCall',
        'toputil_im_plugin_qq'       => 'QQ',
        'toputil_im_plugin_wangwang' => '旺旺'
    );

    /**
     * 配置页面
     */
    public function configPage()
    {
        $pagedata['im_enable'] = app::get('sysconf')->getConf('im.enable');
        $pagedata['im_plugin'] = app::get('sysconf')->getConf('im.plugin');
        $pagedata['im_account_qq'] = app::get('sysconf')->getConf('im.account.qq');
        $pagedata['im_account_wangwang'] = app::get('sysconf')->getConf('im.account.wangwang');

        $pagedata['plugin_options'] = $this->plugins;

        return $this->page('sysim/config.html', $pagedata);
    }

    public function save()
    {
        $request = input::get();

        try{
            app::get('sysconf')->setConf('im.enable',           $request['im_enable']          );
            app::get('sysconf')->setConf('im.plugin',           $request['im_plugin']          );
            app::get('sysconf')->setConf('im.account.qq',       $request['im_account_qq']      );
            app::get('sysconf')->setConf('im.account.wangwang', $request['im_account_wangwang']);
        }catch(Exception $e){
            return $this->splash('error',null,$e->getMessage());
        }

        return $this->splash('success',null,app::get('sysim')->_("保存成功"));
    }

}

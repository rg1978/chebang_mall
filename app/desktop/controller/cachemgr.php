<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_ctl_cachemgr extends desktop_controller 
{

    /**
     * 页面 缓存管理
     *
     * @return \base_http_response
     *
     */
    public function status() 
    {
        $storeResources = array_map(function($store) {
            $store['config'] = array_map(function ($itemConfig) {
                if (is_array($itemConfig)) {
                    $itemConfig = json_encode($itemConfig, 1);
                }
                return $itemConfig;
            }, $store['config']);
            return $store;
        }, cache::getStoreResourcesConfig());

        $pagedata['stores'] = cache::getStoreConfig();
        $pagedata['storeResources']  = $storeResources;
        $pagedata['nullResource']  = cache::getNullResource();
        return $this->page('desktop/cachemgr/index.html', $pagedata);
    }

    /**
     * 动作 清除资源缓存
     *
     * @return \base_http_response
     *
     */
    public function clean() 
    {

        $resource = input::get('resource');
        try
        {
            cache::resource($resource)->flush();

            $storeResource = cache::getStoreConfig('session')['resource'] ?: cache::getStoreConfig(cache::getDefaultDriver())['resource'];
            if ($resource == $storeResource)
            {
                return redirect::route('shopadmin');
            }

            $this->splash('success', null, '缓存清理成功!');
            
            
        }
        catch(Exception $e)
        {
            $this->splash('error', null, $e->getMessage());
        }
    }
}

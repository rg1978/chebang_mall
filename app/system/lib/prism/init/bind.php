<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author guocheng
 */

class system_prism_init_bind extends system_prism_init_base
{

    public function bind()
    {
        $conn = system_prism_init_util::getAdminConn();

        $bindInfo = config::get('apis.depends');
        foreach($bindInfo as $appName=>$apiList)
        {

            if(! (in_array(app::get($appName)->define('type'), config::get('prism.appPushArea'))  || (in_array($appName, ['openstandard'])) ) )
            {
                continue;
            }
            foreach($apiList as $apiName=>$apiInfo)
            {
                if(is_array($apiInfo['path']))
                {
                    foreach($apiInfo['path'] as $path)
                    {
                        $apiName = $apiInfo['appName'];
                        $params = array(
                            'app_id' => system_prism_init_util::getAppId($appName),
                            'api_id' => system_prism_init_util::getApiId($apiName),
                            'path'   => $path,
                            'limit_count' => $apiInfo['limit_count'],
                            'limit_seconds' => $apiInfo['limit_seconds'],
                        );
                        $this->call($conn, '/api/platform/manageapp/bind', $params, 'post');
                    }
                }
                else
                {
                    $apiName = $apiInfo['appName'];
                    $params = array(
                        'app_id' => system_prism_init_util::getAppId($appName),
                        'api_id' => system_prism_init_util::getApiId($apiName),
                        'path'   => $apiInfo['path'],
                        'limit_count' => $apiInfo['limit_count'],
                        'limit_seconds' => $apiInfo['limit_seconds'],
                    );
                    $this->call($conn, '/api/platform/manageapp/bind', $params, 'post');
                }
            }
        }

        return true;
    }

}


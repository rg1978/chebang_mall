<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

class debugbar_serviceProvider extends base_support_serviceProvider
{
    public function register()
    {
        
    }

    public function boot()
    {
        $enabled = config::get('debugbar.enabled');

        if (is_null($enabled)) {
            $enabled = config::get('app.debug', false);
        }

        if (!$enabled) {
            return;
        }

        if (kernel::runningInConsole() || !kernel::is_online()) {
            return;
        }
        
        $debugbar = debugbar::instance();

        $this->registerRouteGroup();
        
        $debugbar->boot();
        
        $this->registerMiddleware('debugbar_middleware_debugbar');
    }

    protected function registerRouteGroup()
    {
        $routeConfig = [
            'prefix' => config::get('debugbar.route_prefix'),
        ];
        route::group($routeConfig, function() {
            route::get('open', [
                'uses' => 'debugbar_ctl_openHandlerController@handle',
                'as' => 'debugbar.openhandler',
            ]);

            route::get('assets/stylesheets', [
                'uses' => 'debugbar_ctl_assetController@css',
                'as' => 'debugbar.assets.css',
            ]);

            route::get('assets/javascript', [
                'uses' => 'debugbar_ctl_assetController@js',
                'as' => 'debugbar.assets.js',
            ]);
            
        });
    }

    public function registerMiddleware($middleware)
    {
        route::middleware($middleware);
    }
}


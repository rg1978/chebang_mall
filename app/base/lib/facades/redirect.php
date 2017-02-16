<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_facades_redirect extends base_facades_facade
{
    /**
	 * The routing redirector instance
	 *
	 * @var base_routing_redirector
	 */    
    private static $__redirect;

    /**
     * {@inheritDoc}
     */    
    protected static function getFacadeAccessor() {
        if (!static::$__redirect)
        {
            static::$__redirect = new base_routing_redirector(url::instance());
        }
        return static::$__redirect;
    }
    
}

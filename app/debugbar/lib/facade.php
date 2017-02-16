<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

class debugbar_facade extends base_facades_facade
{
	/**
	 * The events dispatcher instance
	 *
	 * @var base_events_dispatcher
	 */    
    private static $debugbar;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor() {
        if (!static::$debugbar)
        {
            static::$debugbar = new debugbar_debugbar();
        }
        return static::$debugbar;
    }    
}

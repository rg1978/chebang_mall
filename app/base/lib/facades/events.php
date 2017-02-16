<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * @see base_events_dispatcher
 */
class base_facades_events extends base_facades_facade
{
	/**
	 * The events dispatcher instance
	 *
	 * @var base_events_dispatcher
	 */    
    private static $event;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor() {
        if (!static::$event)
        {
            static::$event = new base_events_dispatcher();
        }
        return static::$event;
    }
}


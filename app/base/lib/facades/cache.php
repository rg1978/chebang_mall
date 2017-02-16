<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * @see base_cache_manager
 */
class base_facades_cache extends base_facades_facade
{
	/**
	 * The cache manager instance
	 *
	 * @var base_cache_manager
	 */
    private static $__cache;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__cache)
        {
            static::$__cache = new base_cache_manager;
        }
        return static::$__cache;
    }
}

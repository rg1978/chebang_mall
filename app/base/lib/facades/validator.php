<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */



class base_facades_validator extends base_facades_facade
{
    /**
	 * The cache manager instance
	 *
	 * @var base_cache_manager
	 */
    private static $__validator;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__validator)
        {
            static::$__validator = new base_validator_factory();
        }
        return static::$__validator;
    }
}

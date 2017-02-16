<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class base_facades_specialutils extends base_facades_facade
{
    /**
	 * The special utils instance
	 *
	 * @var base_utils_specialutils
	 */
    private static $__specialutils;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__specialutils)
        {
            static::$__specialutils = new base_utils_specialutils();
        }
        return static::$__specialutils;
    }
}

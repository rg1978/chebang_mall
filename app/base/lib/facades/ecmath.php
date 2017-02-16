<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */



class base_facades_ecmath extends base_facades_facade
{
    /**
	 * The ecmath instance
	 *
	 * @var base_cache_manager
	 */
    private static $__ecmath;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__ecmath)
        {
            static::$__ecmath = kernel::single('ectools_math');
        }
        return static::$__ecmath;
    }
}

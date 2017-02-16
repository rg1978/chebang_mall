<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/**
 * @see base_hashing_hasher_bcrypt
 */
class base_facades_hash extends base_facades_facade
{
	/**
	 * The hash instance
	 *
	 * @var base_hashing_hasher_bcrypt
	 */    
    private static $__hasher;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__hasher)
        {
            static::$__hasher = kernel::single('base_hashing_hasher_bcrypt');
        }
        return static::$__hasher;
    }
}

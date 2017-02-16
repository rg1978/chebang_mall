<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * @see base_hashing_hasher_bcrypt
 */
class base_facades_apiUtil extends base_facades_facade
{
	/**
	 * The api util instance
	 *
	 * @var base_prism_util
	 */
    private static $__apiUtil;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__apiUtil)
        {
            static::$__apiUtil = new base_prism_util();
        }
        return static::$__apiUtil;
    }
}

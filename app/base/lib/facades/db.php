<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use Doctrine\DBAL\Configuration;

/**
 * @see base_database_manager
 */
class base_facades_db extends base_facades_facade
{
	/**
	 * The database manager instance
	 *
	 * @var base_database_manager
	 */
    private static $__db;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__db)
        {
            $configuration = new Configuration();
            $logger = new base_database_logger();
            $configuration->setSQLLogger($logger);
            static::$__db = new base_database_manager($configuration);
            
        }
        return static::$__db;
    }
}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use Monolog\Logger as Monolog;
use base_log_writer as Writer;


class base_facades_log extends base_facades_facade
{
	/**
	 * The logger instance
	 *
	 * @var Monolog
	 */    
    protected static $__log;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__log)
        {
            static::$__log = new Writer(
                new Monolog(kernel::environment())
            );
            static::configureHandlers(static::$__log);
        }
        
        return static::$__log;
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected static function configureHandlers(Writer $log)
    {
        $method = 'configure'.ucfirst(config::get('log.log')).'Handler';
        static::{$method}($log);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected static function configureSingleHandler(Writer $log)
    {
        $log->useFiles(DATA_DIR.'/logs/luckymall.php', config::get('log.record_level'));
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected static function configureDailyHandler(Writer $log)
    {
        $log->useDailyFiles(DATA_DIR.'/logs/luckymall.php', 30, config::get('log.record_level'));
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSyslogHandler(Writer $log)
    {
        $log->useSyslog('luckymall', config::get('log.record_level'));
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
	protected static function configureErrorlogHandler(Writer $log)
	{
		$log->useErrorLog(config::get('log.record_level'));
	}
    
}


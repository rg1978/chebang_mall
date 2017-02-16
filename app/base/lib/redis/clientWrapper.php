<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use Predis\Client;

class base_redis_clientWrapper extends Client
{
    static private $useCount = 0;

    static private $scriptMaps = null;

    public function loadScripts($names)
    {
        $scriptMaps = static::$scriptMaps ? : (static::$scriptMaps = config::get('redis.scripts'));
        foreach((array)$names as $name)
        {
            if ($class = $scriptMaps[$name])
            {
                $this->getProfile()->defineCommand($name, $class);
            }
        }
    }
    public function __call($commandID, $arguments) {
        static::$useCount++;
        logger::debug(sprintf('REDIS:%d %s, arguments: %s', static::$useCount, $commandID, var_export($arguments, 1)));
        return parent::__call($commandID, $arguments);
    }
}
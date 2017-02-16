<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author guocheng
 */

class system_prism_store
{
    static public function getPrismkey()
    {
        return 'prismConfig';
    }

    public function set($key, $value)
    {
        return redis::scene('system')->hset(static::getPrismKey(), $key, json_encode($value));
    }

    public function get($key, $default = null)
    {
        $value = json_decode(redis::scene('system')->hget($this->getPrismKey(), $key), true);

        if($value == null)
            return $default;
        else
            return $value;
    }

}


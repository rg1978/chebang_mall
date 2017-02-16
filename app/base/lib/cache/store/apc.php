<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use base_contracts_cache_store as Store;

use base_cache_store_apcWrapper as ApcWrapper;

class base_cache_store_apc implements Store
{
    /**
     * The APC wrapper instance.
     *
     * @var base_cache_store_apcWrapper
     */
    protected $apc;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new APC store.
     *
     * @param  base_cache_store_apcWrapper $apc
     * @param  string  $prefix
     * @return void
     */
    public function __construct(ApcWrapper $apc, $prefix = '')
    {
        $this->apc = $apc;
        $this->prefix = $prefix;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->apc->get($this->prefix.$key);

        if ($value !== false) {
            return $value;
        }
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $this->apc->put($this->prefix.$key, $value, $minutes * 60);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return int|bool
     */
    public function increment($key, $value = 1, $initial = 0 , $minutes)
    {
        return $this->apc->increment($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return int|bool
     */
    public function decrement($key, $value = 1, $initial = 0, $minutes)
    {
        return $this->apc->decrement($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return array|bool
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->apc->delete($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        $this->apc->flush();
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}

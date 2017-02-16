<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use base_contracts_cache_store as Store;
use base_support_arr as Arr;

class base_cache_store_secache implements Store
{
    /**
     * The Secache instance.
     *
     * @var base_cache_store_secacheEngine
     */
    protected $secached;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;
    
    /**
     * Create a new Memcached store.
     *
     * @param  \Memcached  $memcached
     * @param  string      $prefix
     * @return void
     */
    public function __construct($secached, $prefix = '')
    {
        $this->setPrefix($prefix);
        $this->secached = $secached;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->secached->fetch(md5($this->prefix.$key));
        //return Arr::get($this->getPayload(md5($this->prefix.$key)), 'content');
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param  string  $key
     * @return array
     */
    protected function getPayload($key)
    {
        // If the file doesn't exists, we obviously can't return the cache so we will
        // just return null. Otherwise, we'll get the contents of the file and get
        // the expiration UNIX timestamps from the start of the file's contents.
        try {
            if (false===$this->secached->fetch($key, $value)) throw new Exception('Secached fetch fail.');
            $expire = $value['expiration'];
            $content = $value['content'];
        } catch (Exception $e) {
            return ['content' => null, 'time' => null];
        }

        // If the current time is greater than expiration timestamps we will delete
        // the file and return null. This helps clean up the old files and keeps
        // this directory much cleaner for us as old files aren't hanging out.
        if (time() >= $expire)
        {
            $this->forget($key);
            return ['content' => null, 'time' => null];
        }


        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on the cache. We'll round this out.
        $time = ceil(($expire - time()) / 60);

        return compact('content', 'time');
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
        $this->secached->store(md5($this->prefix.$key), $value, $minutes);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function increment($key, $value = 1, $initial = 0, $minutes)
    {
        return $this->secached->increment(md5($this->prefix.$key), $value, $initial, $minutes);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function decrement($key, $value = 1, $initial = 0, $minutes)
    {
        return $this->secached->decrement(md5($this->prefix.$key), $value, $initial, $minutes);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        $this->secached->delete(md5($this->prefix.$key));
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        $this->secached->clean();
    }

    /**
     * Get the expiration time based on the given minutes.
     *
     * @param  int  $minutes
     * @return int
     */
    protected function expiration($minutes)
    {
        if ($minutes === 0)
        {
            return 9999999999;
        }

        return time() + ($minutes * 60);
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

    /**
     * Set the cache key prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }    
}

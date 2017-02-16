<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use base_support_arr as Arr;
use base_cache_store_null as NullStore;
use base_cache_repository as Repository;
use base_contracts_cache_store as Store;
use base_cache_store_secache as SecacheStore;
use base_cache_store_memcached as MemcachedStore;
use base_cache_store_memcachedConnector as MemcachedConnector;
use base_cache_store_apc as ApcStore;
use base_cache_store_apcWrapper as ApcWrapper;

class base_cache_manager
{
    /**
     * The array of resolved cache resources.
     *
     * @var array
     */
    protected $resources = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];
    
    /**
     * The disable Resouce name.
     *
     * @var array
     */
    protected $disableResourceName = 'null';

    /**
     * Cache enabled.
     *
     * @var bool
     */    
    protected $enabled;
    
    /**
     * Create a new Cache manager instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->enabled = (bool)config::get('cache.enabled', false);
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function store($name)
    {
        if (is_null($name)) {
            throw new InvalidArgumentException('Use cache must specify store.');
        }       

        return $this->get($name);
    }

    /**
     * Get a cache driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    public function resource($name)
    {
        return !isset($this->resources[$name]) ? ($this->resources[$name] = $this->resolve($name)) : $this->resources[$name];
    }

    /**
     * Attempt to get the store from the local cache.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function get($name)
    {
        if (!$this->enabled && !in_array($name, config::get('cache.disabled_except', []))) return $this->resource($this->disableResourceName);
            
        $config = $this->getStoreConfig($name);

        if (is_null($config))
        {
            throw new InvalidArgumentException("Cache store [{$name}] is not defined.");
        }

        if (!($resource = $config['resource']))
        {
            $resource = $this->getDefaultResource();
        }

        return $this->resource($resource);
    }

    /**
     * Resolve the given store.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function resolve($name)
    {
        $config = $this->getResourceConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Cache Resource [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        } else {
            return $this->{'create'.ucfirst($config['driver']).'Driver'}($config);
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($config);
    }
    

    /**
     * Create an instance of the APC cache driver.
     *
     * @param  array  $config
     * @return base_cache_repository
     */

    protected function createSecacheDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        $size = Arr::get($config, 'size', '1g');

        $file = Arr::get($config, 'file', 'secache');

        return $this->repository(new SecacheStore(new base_cache_store_secacheEngine(CACHE_DIR, $file, $size), $prefix));
    }

    /**
     * Create an instance of the Null cache driver.
     *
     * @return base_cache_repository
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullStore);
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param  array  $config
     * @return base_cache_repository
     */
    protected function createMemcachedDriver(array $config)
    {
        $prefix = $this->getPrefix($config);

        $memcached = new MemcachedConnector();
        
        $memcached = (new MemcachedConnector)->connect($config['servers']);

        return $this->repository(new MemcachedStore($memcached, $prefix));
    }

    /**
     * Create an instance of the APC cache driver.
     *
     * @param  array  $config
     * @return base_cache_repository
     */
    protected function createApcDriver(array $array)
    {
        $prefix = $this->getPrefix($config);

        return $this->repository(new ApcStore(new ApcWrapper, $prefix));
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  \Illuminate\Contracts\Cache\Store  $store
     * @return \Illuminate\Cache\Repository
     */
    public function repository(Store $store)
    {
        $repository = new Repository($store);
        return $repository;
    }

    /**
     * Get the cache prefix.
     *
     * @param  array  $config
     * @return string
     */
    protected function getPrefix(array $config)
    {
        return Arr::get($config, 'prefix') ?: config::get('cache.prefix');
    }

    /**
     * Get the cache store configuration.
     *
     * @param  string  $name
     * @return array
     */
    public function getStoreConfig($name)
    {
        if (is_null($name)) return config::get("cache.stores");
        
        return config::get("cache.stores.{$name}");
    }

    /**
     * Get the cache resource configuration.
     *
     * @param  string  $name
     * @return array
     */
    public function getResourceConfig($name = null)
    {
        if (is_null($name)) return config::get("cache.resources");
            
        return config::get("cache.resources.{$name}");
    }

    /**
     * Get the default cache resource name.
     *
     * @return string
     */
    public function getDefaultResource()
    {
        return config::get('cache.default');
    }

    /**
     * Get the null cache resource name.
     *
     * @return string
     */
    public function getNullResource()
    {
        return 'null';
    }

    /**
     * Set the default cache driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        config::set('cache.default');
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->store(), $method], $parameters);
    }

    /**
     * Get stores config with array data.
     *
     * @return array
     */
    public function getStoreResourcesConfig()
    {
        $config = config::get('cache');
        $resources = collect($config['resources']);
        $stores = collect($config['stores']);
       
        $stores->each(function($storeConfig, $storeName) use (&$storeResources, $resources) {

            $resourceName = ($storeConfig['resource']) ?: $this->getDefaultResource();

            if ($resourceName && $resources->has($resourceName))
            {
                if (!$storeResources[$resourceName])
                {
                    $storeResources[$resourceName] = [
                        'name' => $resourceName,
                        'config' => $resources->get($resourceName),
                        'driver' => $resources->get($resourceName)['driver'],
                    ];
                }
                $storeResources[$resourceName]['supports'][] = ['title' => $storeConfig['title'], 'name' => $storeName];
            }
        });

        return $storeResources;
    }
}

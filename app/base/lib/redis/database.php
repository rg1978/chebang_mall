<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use Closure;
use Predis\Client;
use base_redis_sceneClient as sceneClient;
use base_support_arr as Arr;

class base_redis_database
{
   /**
     * The scene clients instance.
     *
     * @var array
     */
    protected $sceneClients;
    
    /**
     * The clients instance.
     *
     * @var array
     */
    protected $clients;

    /**
     * Create a new Redis connection instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    /**
     * Get a specific Redis connection instance by scene.
     *
     * @param  string  $name
     * @return \Predis\ClientInterface|null
     */
    public function scene($name = null)
    {
        if (isset($this->sceneClients[$name])) return $this->sceneClients[$name];

        if (is_null($connName = config::get('redis.scenes.'.$name.'.connection', null)))
        {
            throw new InvalidArgumentException("Redis scene [$name] connection is not defined");
        }

        $this->sceneClients[$name] = new sceneClient($name, $this->connection($connName));
        
        return $this->sceneClients[$name];
        //return $this->connection($connName, ['prefix' => $name.':']);
    }

    /**
     * Get a specific Redis connection instance.
     *
     * @param  string  $name
     * @return \Predis\ClientInterface|null
     */
    protected function connection($name, $options = [])
    {
        if (isset($this->clients[$name])) return $this->clients[$name];

        if (is_null($connectionConfig = config::get('redis.connections.'.$name)))
        {
            throw new InvalidArgumentException("Redis connection [$name] is not defined");
        }

        $servers = (array) array_get($connectionConfig, 'servers');

        $configOptions = (array) array_get($connectionConfig, 'options');

        $options = $options + $configOptions;

        // 如果存在多个server配置, 默认为集群
        if (isset($servers[1]))
        {
            return $this->clients[$name] = new Client($servers, $options);
        }
        else
        {
            return $this->clients[$name] = new Client(array_pop($servers), $options);
        }
    }

    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @param  string  $scene
     * @param  string  $method
     * @return void
     */
    public function subscribe($channels, Closure $callback, $scene, $method = 'subscribe')
    {
        $loop = $this->scene($scene)->pubSubLoop();

        call_user_func_array([$loop, $method], (array) $channels);

        foreach ($loop as $message) {
            if ($message->kind === 'message' || $message->kind === 'pmessage') {
                call_user_func($callback, $message->payload, $message->channel);
            }
        }

        unset($loop);
    }

    /**
     * Subscribe to a set of given channels with wildcards.
     *
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @param  string  $scene
     * @return void
     */
    public function psubscribe($channels, Closure $callback, $scene = null)
    {
        return $this->subscribe($channels, $callback, $scene, __FUNCTION__);
    }

    public function flushAllResources()
    {
        $connections = config::get('redis.connections');
        foreach (array_keys($connections) as $connName)
        {
            $this->connection($connName)->flushdb();
        }
    }
}

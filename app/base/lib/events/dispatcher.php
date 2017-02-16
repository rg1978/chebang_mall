<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

//base_contracts_queue_shouldQueue

class base_events_dispatcher
{
    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * The sorted event listeners.
     *
     * @var array
     */
    protected $sorted = [];

    /**
     * The event firing stack.
     *
     * @var array
     */
    protected $firing = [];

    /**
     * 异步事件任务指定的队列
     *
     * @var array
     */
    protected $queues = [];


    /**
     * 是否需要初始化事件任务
     */
    protected $initEvents = true;

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  string|array  $events
     * @param  mixed  $listener
     * @param  string $sync   sync 同步｜async 异步
     * @param  int  $priority
     * @param  string $queue 如果为异步可指定执行的队列
     * @return void
     */
    public function listen($events, $listener, $sync='sync', $priority = 0, $queue)
    {
        foreach ((array) $events as $event)
        {
            //目前闭包监听只支持同步
            if(is_string($listener) )
            {
                list($class, $method) = $this->parseClassCallable($listener);
                $objClass = new $class();
            }

            if( $sync == 'async' && $this->__listenerIsSupportAsync($objClass) )
            {
                $this->queues[$listener] = $queue ? $queue : 'system_tasks_events';
                $this->listeners[$event]['async'][$priority][] = $listener;
            }
            else
            {
                if( is_string($listener) )
                {
                    $this->listeners[$event]['sync'][$priority][] = $this->createClassListener($objClass, $method);
                }
                else
                {
                    $this->listeners[$event]['sync'][$priority][] = $listener;
                }
            }

            unset($this->sorted[$event]);
        }
    }

    /**
     * listener是否支持异步调用
     */
    private function __listenerIsSupportAsync($objClass)
    {
        if( $objClass instanceof base_events_interface_queue )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function createClassListener($objClass, $method)
    {
        return function () use ($objClass, $method) {
            return call_user_func_array(
                [$objClass, $method], func_get_args()
            );
        };
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]);
    }

    /**
     * Fire an event until the first non-null response is returned.
     *
     * @param  string|object  $event
     * @param  array  $params
     * @return mixed
     */
    public function until($event, $params= [])
    {
        return $this->fire($event, $params, true);
    }

    /**
     * 执行指定的事件任务
     *
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function push($event, $listener, $params = [] )
    {
        $this->forget($event);
        $this->initEvents = false;

        $this->listen($event, $listener);
        $this->fire($event, $params);
    }

    /**
     * 是否需要初始化事件任务
     */
    public function isInitEvents($init=true)
    {
        return $this->initEvents = $init;
    }

    /**
     * Get the event that is currently firing.
     *
     * @return string
     */
    public function firing()
    {
        return last($this->firing);
    }

    private function __preInitEvents($eventName)
    {
        if( $this->initEvents )
        {
            $EventService = kernel::single('base_events_service');
            $EventService->setListens($eventName)->boot();
        }
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $eventName
     * @param  array $params
     * @param  bool  $halt
     * @return array|null
     */
    public function fire($eventName, $params, $halt = false)
    {
        $this->__preInitEvents($eventName);

        $responses = [];

        $this->firing[] = $eventName;

        if( ! is_array( $params ) )
        {
            $params = [$params];
        }

        $listeners = $this->getListeners($eventName);

        //执行同步listeners
        foreach ($listeners['sync'] as $key=>$listener)
        {
            $response = call_user_func_array($listener, $params );

            if (! is_null($response) && $halt)
            {
                array_pop($this->firing);
                return $response;
            }

            if ($response === false)
            {
                break;
            }

            $responses[] = $response;
        }

        $this->__fireAsync($eventName, $params, $listeners['async']);

        array_pop($this->firing);

        //执行完一个事件后，重置需要初始化
        $this->isInitEvents(true);

        return $responses;
    }

    /**
     * 执行异步事件任务
     *
     * @param $eventName 事件名称
     * @param $params 事件参数
     * @param $asyncListeners 事件的异步listener
     */
    private function __fireAsync($eventName, $params, $asyncListeners)
    {
        if( empty($asyncListeners) ) return true;

        $queueParams['eventParams'] = $params;
        $queueParams['eventName'] = $eventName;

        //执行异步listeners , 将异步listeners加入到队列
        if( count($asyncListeners) == 1 )
        {
            $listener = current($asyncListeners);
            $queueParams['listener'] = $listener;
            system_queue::instance()->publish($this->queues[$listener], $this->queues[$listener], $queueParams);
        }
        else
        {
            $queueParams['listeners'] = $asyncListeners;
            $queueParams['queues'] = $this->queues;
            system_queue::instance()->publish('system_tasks_distrEvents', 'system_tasks_distrEvents', $queueParams);
        }

        return true;
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param  string  $eventName
     * @return array
     */
    public function getListeners($eventName)
    {
        if (! isset($this->sorted[$eventName]))
        {
            $this->sortListeners($eventName);
        }

        return $this->sorted[$eventName];
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param  string  $eventName
     * @return array
     */
    protected function sortListeners($eventName)
    {
        $this->sorted[$eventName] = [];

        if (isset($this->listeners[$eventName]))
        {
            foreach( ['sync', 'async'] as $type )
            {
                $this->__preSortListeners($eventName, $type);
            }
        }
    }

    private function __preSortListeners($eventName, $type)
    {
        if( $this->listeners[$eventName][$type] )
        {
            krsort($this->listeners[$eventName][$type]);
            $this->sorted[$eventName][$type] = call_user_func_array(
                'array_merge', $this->listeners[$eventName][$type]
            );
        }
    }

    /**
     *  返回listener的处理类和处理方法
     *
     * @param  string  $listener
     * @return array
     */
    protected function parseClassCallable($listener)
    {
        $segments = explode('@', $listener);

        return [$segments[0], count($segments) == 2 ? $segments[1] : 'handle'];
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $event
     * @return void
     */
    public function forget($event)
    {
        unset($this->listeners[$event], $this->sorted[$event]);
    }
}


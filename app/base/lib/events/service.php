<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_events_service {

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    public function setListens($eventName)
    {
        if( $eventName )
        {
            $listen = config::get('events.listen');
            $this->listen[$eventName] = $listen[$eventName];
        }

        return $this;
    }

    /**
     * 注册触发事件的listen
     *
     * @return void
     */
    public function boot()
    {
        //调用注册后，不需要再初始化
        event::isInitEvents(false);

        foreach ((array)$this->listen as $event => $listeners)
        {
            foreach( (array)$listeners as $listenerArr )
            {
                list( $listener, $isSync, $priority ) = $listenerArr;
                $isSync = $isSync ? : 'sync';
                $priority = $priority ? : 0;

                $queue = ($isSync == 'async') ? $listenerArr['queue'] : null;//指定执行队列

                event::listen($event, $listener, $isSync, $priority, $queue);
            }
        }
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }
}


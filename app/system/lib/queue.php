<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class system_queue {

    static private $__instance = null;

    static private $__config = null;

    private $__controller = null;

    static private function __init()
    {
        if (!isset(self::$__config))
        {
            self::$__config['queues']   = (array)config::get('queue.queues', array());
            self::$__config['bindings'] = (array)config::get('queue.bindings', array());
            self::$__config['action']   = (array)config::get('queue.action', array());
        }
    }

    static public function get_config($key=null)
    {
        if (!is_null($key))
        {
            return self::$__config[$key];
        }
        return self::$__config;
    }

    public function __construct()
    {
        self::__init();
        $controller = self::get_driver_name();
        $this->set_controller(new $controller);
    }

    static public function get_driver_name()
    {
        return config::get('queue.default', 'system_queue_adapter_mysql');
    }

    public function get_controller()
    {
        return $this->__controller;
    }

    public function set_controller($controller)
    {
        if ($controller instanceof system_interface_queue_adapter)
        {
            $this->__controller = $controller;
        }
        else
        {
            throw new Exception('this instance must implements system_interface_queue_adapter');
        }
    }

    static public function get_queue($queue_name)
    {
        if (isset(self::$__config['queues'][$queue_name]))
        {
            return self::$__config['queues'][$queue_name];
        }
        return false;
    }

    static public function get_queues()
    {
        return self::$__config['queues'];
    }

    static public function get_bindings()
    {
        return self::$__config['bindings'];
    }

    static public function instance()
    {
        if (!isset(self::$__instance))
        {
            self::$__instance = new system_queue;
        }
        return self::$__instance;
    }

    static private function __get_publish_queues($exchange_name)
    {
        if (!isset(self::$__config['bindings'][$exchange_name]))
        {
            $default_publish_queue = config::get('queue.default_publish_queue');
            return array($default_publish_queue);
        }
        return self::$__config['bindings'][$exchange_name];
    }

    static public function __get_push_workers($action)
    {
        $action = (array)config::get('queue.action', array());
        return $action[$action];
    }

    public function publish($exchange_name, $worker, $params=array())
    {
        $queues = $this->__get_publish_queues($exchange_name);
        foreach($queues as $queue_name)
        {
            $queue_data = array(
                'queue_name' => $queue_name,
                'worker' => $worker,
                'params' => $params,
            );
            $this->get_controller()->publish($queue_name, $queue_data);
        }
        return true;
    }

    /**
     * 创建一个延时队列
     *
     * @param string $worker 队列执行类
     * @param array $params 队列执行参数
     * @param int $delay 延时队列延时时间 单位秒
     */
    static public function later($exchange_name, $worker, $params, $delay)
    {
        $instance = self::instance();
        $queues = self::__get_publish_queues($exchange_name);
        foreach($queues as $queue_name)
        {
            $queue_data = array(
                'queue_name' => $queue_name,
                'worker' => $worker,
                'params' => $params,
            );
            self::instance()->get_controller()->later($delay, $queue_data, $queue_name);
        }
        return true;
    }

    static public function action($action, $params)
    {
        $workers = self::__get_push_workers($action);
        self::bulk($workers, $params);
        return true;
    }

    static public function bulk($workers, $params)
    {
        foreach ($workers as $exchange_name=>$worker)
        {
            self::push($worker, $worker, $params);
        }
        return true;
    }

    static public function push($exchange_name, $worker, $params=array())
    {
        $instance = self::instance();
        $queues = self::__get_publish_queues($exchange_name);
        foreach($queues as $queue_name)
        {
            $queue_data = array(
                'queue_name' => $queue_name,
                'worker' => $worker,
                'params' => $params,
            );
            self::instance()->get_controller()->publish($queue_name, $queue_data);
        }
        return true;
    }

    /**
     * 获取队列数据
     *
     * @param $queueName string 队列名称
     * @param $maxExecTime int  队列进程执行时间
     *
     * @return $queueMessage object pop队列返回的队列处理类
     */
    public function get($queueName, $maxExecTime)
    {
        if( $maxExecTime )
        {
            $this->get_controller()->setExpire($maxExecTime);
        }

        $queueMessage = $this->get_controller()->get($queueName);

        if ($queueMessage instanceof system_interface_queue_message)
        {
            return $queueMessage;
        }

        return false;
    }

    /**
     * 确认队列已消费
     *
     * @param $queueMessage object pop队列返回的队列处理类
     */
    public function ack($queueMessage)
    {
        return $queueMessage->ack();
    }

    /**
     * 消费一条队列数据
     *
     * @param $queueMessage object pop队列返回的队列处理类
     */
    public function consumer($queueMessage)
    {
        return $queueMessage->fire();
    }

    /**
     * 清空一个队列
     *
     * @param $queueName string 队列名称
     */
    public function purge($queueName)
    {
        return $this->get_controller()->purge($queueName);
    }

    /**
     * 判断队列是否已经消费完
     *
     * @param $queueName string 队列名称
     */
    public function is_end($queueName)
    {
        return $this->get_controller()->is_end($queueName);
    }
}


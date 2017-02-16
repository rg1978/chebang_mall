<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class system_apilog {

    private $__controller = null;

    public function __construct()
    {
        $controller = self::get_driver_name();
        $this->set_controller(new $controller);
    }

    static public function get_driver_name()
    {
        return config::get('apilog.default', 'system_apilog_adapter_mysql');
    }

    public function get_controller()
    {
        return $this->__controller;
    }

    public function set_controller($controller)
    {
        if ($controller instanceof system_interface_apilog_adapter)
        {
            $this->__controller = $controller;
        }
        else
        {
            throw new Exception('this instance must implements system_interface_apilog_adapter');
        }
    }

    public function create($apiType, $params)
    {
        return $this->__controller->create($apiType, $params);
    }

    public function update($apilogId, $status, $result, $runtime)
    {
        if( !$apilogId ) return false;
        return $this->__controller->update($apilogId, $status, $result, $runtime);
    }

    public function get($apilogId, $fields="*")
    {
        if( !$apilogId ) return false;
        return $this->__controller->get($apilogId, $fields);
    }
}

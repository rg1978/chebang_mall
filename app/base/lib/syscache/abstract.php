<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_syscache_abstract{
    private $_last_modify = null;

    private function _get_prefix()
    {
        return 'syscache_last_modified.'.get_class($this);
    }

    private function getSyscacheKey()
    {
        return 'syscache_last_modified';
    }

    private function getKey()
    {
        return get_class($this);
    }
    
    public function set_last_modify(){
        $last_modify = time();
        if (redis::scene('system')->hset($this->getSyscacheKey(), $this->getKey(), $last_modify)) {
            $this->_last_modify = $last_modify;
            return true;
        }
        return false;
    }

    public function get_last_modify(){
        if (!isset($this->_last_modify)) {
            if ($last_modify = redis::scene('system')->hget($this->getSyscacheKey(), $this->getKey())) {
                $this->_last_modify = $last_modify;
            }else{
                $this->_last_modify = 123450001;
            }
        }
        return $this->_last_modify;
    }
}

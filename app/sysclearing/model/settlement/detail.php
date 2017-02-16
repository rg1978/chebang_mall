<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysclearing_mdl_settlement_detail extends dbeav_model {

  
    public function _filter($filter)
    {
        if($filter['timearea'])
        {
            $timeArray = explode('-', $filter['timearea']);
            $filter['settlement_time|than']  = strtotime($timeArray[0]);
            $filter['settlement_time|lthan'] = strtotime($timeArray[1]);
            unset($filter['timearea']);
        }
        
        if($filter['time_start'])
        {
            $filter ['settlement_time|than'] = strtotime ($filter ['time_start']);
            unset($filter['time_start']);
        }
        
        if($filter['time_end'])
        {
            $filter ['settlement_time|lthan'] = strtotime ($filter ['time_end']);
            unset($filter['time_end']);
        }
        
        if($filter['shop_id'] == '' || $filter['shop_id'] == -1)
        {
            unset($filter['shop_id']);
        }
        
        if($filter['settlement_type'] == '' || $filter['settlement_type'] == -1)
        {
            unset($filter['settlement_type']);
        }
        
        return parent::_filter($filter);
    }
}


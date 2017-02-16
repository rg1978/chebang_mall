<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysclearing_mdl_settlement extends dbeav_model {


    public function _filter($filter)
    {
        if( is_array($filter) &&  $filter['shop_name'] )
        {
            $objMdlShop = app::get('sysshop')->model('shop');
            $adata = $objMdlShop->getList('shop_id',array('shop_name|has'=>$filter['shop_name']));
            if($adata)
            {
                foreach($adata as $key=>$value)
                {
                    $shop[$key] = $value['shop_id'];
                }
                $filter['shop_id'] = $shop;
            }
            unset($filter['shop_name']);
        }

        if($filter['timearea'])
        {
            $timeArray = explode('-', $filter['timearea']);
            $filter['settlement_time|bthan']  = strtotime($timeArray[0]);
            $filter['settlement_time|lthan'] = strtotime($timeArray[1]);
            unset($filter['timearea']);
        }
        if($filter['settlement_status']=='' || $filter['settlement_status'] == -1)
        {
            unset($filter['settlement_status']);
        }

        if($filter['time_start'])
        {
            $filter ['account_start_time|bthan'] = strtotime ($filter ['time_start']);
            unset($filter['time_start']);
        }

        if($filter['time_end'])
        {
            $filter ['account_start_time|lthan'] = strtotime ($filter ['time_end']);
            unset($filter['time_end']);
        }
        if($filter['shop_id']<=0)
        {
            unset($filter['shop_id']);
        }
        return parent::_filter($filter);
    }



}


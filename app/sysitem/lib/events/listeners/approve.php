<?php
/**
 * 关闭商品审核触发事件
 *
 * @author     hlj
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_events_listeners_approve {


    /**
     * @brief 关闭商品审核，未审核及未提交审核商品状态变成下架状态
     * @author hlj
     * @param $status string onsale(上架、出售中) instock(下架、库中)
     *
     * @return bool
     */
    public function approve()
    {
        try
        {
            $params = array('approve_status'=>'instock');
            $result = app::get('topshop')->rpcCall('item.sale.status',$params);

            return $result;
        }
        catch(Exception $e)
        {
            throw  new \LogicException($e->getMessage());
        }
        
    }
}
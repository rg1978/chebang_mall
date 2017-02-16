<?php
/**
 * ShopEx licence
 * - item.store.minus
 * - 用于付款，下单扣减库存
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-06-02
 */
class sysitem_api_item_minusStore{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = "付款，下单扣减库存";

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'item_id'   => ['type'=>'int',     'valid'=>'required', 'title'=>'商品ID',   'example'=>'188', 'desc'=>'商品id'],
            'sku_id'    => ['type'=>'int',     'valid'=>'required', 'title'=>'SKU的ID',  'example'=>'192', 'desc'=>'货品id',],
            'quantity'  => ['type'=>'int',     'valid'=>'required', 'title'=>'SKU数量',  'example'=>'3',   'desc'=>'扣减库存数量',],
            'sub_stock' => ['type'=>'boolean', 'valid'=>'boolean',  'title'=>'减库存方式', 'example'=>'1',   'desc'=>'扣减库存方式(0:付款减库存,1:下单减库存)'],
            'status'    => ['type'=>'string',  'valid'=>'in:afterorder,afterpay', 'title'=>'订单动作', 'example'=>'afterorder', 'desc'=>'订单动作(afterorder,afterpay)，下单后和支付后'],
        );
        return $return;
    }

    /**
     * 付款，下单扣减库存
     * @desc 用于付款，下单扣减库存
     * @return boolean 
     */
    public function storeMinus($params)
    {
        $subStock = $params['sub_stock'];
        $status = $params['status'];

        switch ($status) {
            case 'afterorder':
                $this->__afterorder($params);
                break;
            case 'afterpay':
                $this->__afterpay($params);
                break;
        }
        return true;
    }


    /**
     * 处理下单后的库存
     * @param  array $params 处理库存相关值
     * @return boolean
     */
    private function __afterorder($params)
    {
        $objLibStore = kernel::single('sysitem_trade_store');
        if ($params['sub_stock'])
        { //下单减库存，直接扣减库存
            if(!$objLibStore->minusItemStore($params))
            {
                $msg = app::get('sysitem')->_('商品库存不足');
                throw new \LogicException($msg);
            }
        }
        else
        { // 付款减库存，库存字段不动，冻结库存字段扣减(freez)
            if(!$is_freez = $objLibStore->freezeItemStore($params))
            {
                $msg = app::get('sysitem')->_('商品库存不足');
                throw new \LogicException($msg);
            }
            event::fire('update.item', array($params['item_id']));
        }
        return true;
    }

    /**
     * 处理支付后的库存
     * @param  array $params 处理库存相关值
     * @return boolean
     */
    private function __afterpay($params)
    {
        $objLibStore = kernel::single('sysitem_trade_store');
        if ($params['sub_stock'])
        { //下单减库存,支付后不用做任何处理，直接返回true
            return true;
        }
        else
        { // 付款减库存，支付后需要处理冻结库存
            $result = $objLibStore->minusItemStoreAfterPay($params);
            if(!$result)
            {
                $msg = app::get('sysitem')->_('库存扣减失败');
                throw new \LogicException($msg);
                return false;
            }
            event::fire('update.item', array($params['item_id']));
        }
        return true;
    }

}

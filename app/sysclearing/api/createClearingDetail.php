<?php
class sysclearing_api_createClearingDetail{
    public $apiDescription = "创建结算单明细";
    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单编号'],
            'settlement_type' => ['type'=>'string', 'valid'=>'', 'default'=>'1', 'example'=>'','description'=>'结算类型'],
        );
        return $return;
    }
    public function add($params)
    {
        //.oid,orders.tid,orders.shop_id,orders.pay_time,orders.item_id,orders.sku_id,orders.bn,orders.title,orders.spec_nature_info,orders.price,orders.num,orders.sku_properties_name,orders.divide_order_fee,orders.part_mjz_discount,orders.payment,orders.refund_fee,orders.cat_service_rate,orders.discount_fee,orders.adjust_fee,
        $fidlds = "pay_time,post_fee,orders.*";
        $tradeInfo = app::get('sysclearing')->rpcCall('trade.get',array('tid'=>$params['tid'],'fields'=>$fidlds));
        $objClearingSettlement = kernel::single('sysclearing_settlement');
        if(!$params['settlement_type'])
        {
            $params['settlement_type'] = 1;
        }
        $result = $objClearingSettlement->generate($tradeInfo,$params['settlement_type']);
        return $result;
    }
}

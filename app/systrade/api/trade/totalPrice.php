<?php
class systrade_api_trade_totalPrice{
    public $apiDescription = "计算订单包含运费后的金额";
    public function getParams()
    {
        $return['params'] = array(
            'total_fee' => ['type'=>'money', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单总额'],
            'total_weight' => ['type'=>'float', 'valid'=>'required|numeric', 'default'=>'', 'example'=>'','description'=>'订单总重量'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'店铺id'],
            'shipping_type' => ['type'=>'string', 'valid'=>'required', 'default'=>'express', 'example'=>'','description'=>'配送类型'],
            'region_id' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'收货地址编号'],
            'usedCartPromotionWeight' => ['type'=>'float', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'收货地址编号'],
            'discount_fee' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>''],
            'usedToPostage' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'运费信息'],
        );
        return $return;
    }
    public function total($params)
    {
        $objLibTradeTotal = kernel::single('systrade_data_trade_total');
        $data = $objLibTradeTotal->trade_total_method($params);
        return $data;
    }
}

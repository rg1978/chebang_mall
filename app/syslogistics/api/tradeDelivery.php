<?php
class syslogistics_api_tradeDelivery {

    public $apiDescription = "对指定订单进行发货，交易发货(OMS发货)";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单号'],
            'corp_code' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'物流公司编号'],
            'corp_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'运单号'],
            'ziti_memo' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'自提备注'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'店铺id'],
            'seller_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'商家操作员id'],
            'memo' =>['type'=>'string','valid'=>'', 'description'=>'备注','default'=>'','example'=>'1'],
        );
        return $return;
    }

    public function deliveryTrade($params)
    {
        $params['logi_no'] = $params['corp_no'];
        unset($params['corp_no']);
        $res = app::get('syslogistics')->rpcCall('trade.delivery',$params);
        return true;
    }
}

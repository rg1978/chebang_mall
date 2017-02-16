<?php
class systrade_api_trade_delivery {

    public $apiDescription = "对指定订单进行发货，交易发货";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单号'],
            'corp_code' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'物流公司编号'],
            'logi_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'运单号'],
            'ziti_memo' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'自提备注'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'店铺id'],
            'seller_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'商家操作员id'],
            'memo' =>['type'=>'string','valid'=>'', 'description'=>'备注','default'=>'','example'=>'1'],
        );
        return $return;
    }

    public function deliveryTrade($params)
    {
        $tid = $params['tid'];
        $corpCode = $params['corp_code'];
        $logiNo = $params['logi_no'];
        $zitiMemo = $params['ziti_memo'];
        $memo = $params['memo'];
        $shopUserData = [
            'shop_id'=>$params['shop_id'],
            'seller_id'=>$params['seller_id'],
        ];
        unset($params);
        return kernel::single('systrade_data_trade_delivery')->doDelivery($tid, $corpCode, $logiNo, $shopUserData, $zitiMemo, $memo);
    }
}

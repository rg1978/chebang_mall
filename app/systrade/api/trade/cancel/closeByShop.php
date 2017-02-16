<?php

class systrade_api_trade_cancel_closeByShop {

    public $apiDescription = "商家取消订单";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'example'=>'','description'=>'订单id'],
            'cancel_reason' => ['type'=>'string', 'valid'=>'required|max:50', 'example'=>'','description'=>'订单取消原因'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'example'=>'','description'=>'订单所属店铺id'],

            'refund_bn' => ['type'=>'string','valid'=>'', 'description'=>'退款申请单编号'],
            'return_freight' => ['type'=>'bool','valid'=>'string', 'description'=>'是否返还运费("true":退运费；"false":不退运费)'],
        );

        return $return;
    }

    public function close($params)
    {
        $shopId = $params['shop_id'];
        $cancelReason = $params['cancel_reason'];

        kernel::single('systrade_data_trade_cancel')
            ->setCancelFromType('shop')
            ->setCancelId($shopId)
            ->create($params['tid'], $cancelReason, $params['refund_bn'],$params['return_freight']);

        return ['tid'=>$params['tid']];
    }
}

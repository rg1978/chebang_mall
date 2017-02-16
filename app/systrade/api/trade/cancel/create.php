<?php

class systrade_api_trade_cancel_create {

    public $apiDescription = "用户申请取消订单/平台强制取消订单";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'example'=>'','description'=>'订单id'],
            'cancel_reason' => ['type'=>'string', 'valid'=>'required|max:50', 'example'=>'','description'=>'订单取消原因'],
            //如果开放API user_id 通过oauth赋值
            'user_id' => ['type'=>'int', 'valid'=>'', 'example'=>'','description'=>'订单所属用户id'],
        );

        return $return;
    }

    public function cancelTrade($params)
    {
        $cancelReason = trim($params['cancel_reason']);

        $cancelFromType = $params['user_id'] ? 'buyer' : 'shopadmin';

        kernel::single('systrade_data_trade_cancel')
            ->setCancelFromType($cancelFromType)
            ->setCancelId($params['user_id'])
            ->create($params['tid'], $cancelReason);

        return ['tid'=>$params['tid']];
    }
}

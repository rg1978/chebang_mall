<?php

class systrade_api_trade_ziti_sendDeliveryVcode {

    public $apiDescription = "自提订单短信发送提货码";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单id'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单所属店铺id'],
        );
        return $return;
    }

    public function send($params)
    {
        if( ! kernel::single('systrade_messager')->sendZitiDelivery($params['tid'], $params['shop_id']) )
        {
            $msg = app::get('systrade')->_('提货码发送失败');
            throw new \LogicException($msg);
        }
        return true;
    }
}


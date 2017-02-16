<?php

class systrade_api_trade_ziti_verifyDeliveryVcode {

    public $apiDescription = "验证自提订单提货码";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单id'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单所属店铺id'],
            'vcode' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'自提订单提货码'],
        );
        return $return;
    }

    public function verify($params)
    {
        if( ! kernel::single('systrade_messager')->verifyZitiDelivery($params['tid'], $params['shop_id'], $params['vcode'] ) )
        {
            $msg = app::get('systrade')->_('提货码验证失败');
            throw new \LogicException($msg);
        }

        return true;
    }
}


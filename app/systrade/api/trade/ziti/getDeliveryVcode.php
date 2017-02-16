<?php

class systrade_api_trade_ziti_getDeliveryVcode {

    public $apiDescription = "获取自提订单提货码";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单id'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单所属店铺id'],
        );
        return $return;
    }

    public function get($params)
    {
        $tids = explode(',',$params['tid']);
        $data = app::get('systrade')->model('delivery_code')->getList('tid,shop_id,num,status',['tid'=>$tids,'shop_id'=>$params['shop_id']]);
        return $data ? array_bind_key($data,'tid') : [];
    }
}


<?php
class syslogistics_api_delivery_create{

    public $apiDescription = "创建发货单";
    public function getParams()
    {
        $return['params'] = array(
            'tid' =>['type'=>'string','valid'=>'required', 'description'=>'订单号','default'=>'','example'=>'1'],
            'oids' =>['type'=>'string','valid'=>'required', 'description'=>'子订单集合','default'=>'','example'=>'1'],
            'seller_id' =>['type'=>'int','valid'=>'required', 'description'=>'卖家id','default'=>'','example'=>'1'],
            'shop_id' =>['type'=>'int','valid'=>'required', 'description'=>'订单所属店铺','default'=>'','example'=>'1'],
        );
        return $return;
    }
    public function create($params)
    {
        $fields = "tid,receiver_name,user_id,shop_id,receiver_mobile,receiver_state,receiver_district,receiver_address,receiver_city,need_invoice,invoice_type,invoice_name,invoice_main,post_fee,orders.tid,orders.oid,orders.sku_id,orders.bn,orders.title,orders.num,orders.gift_data";
        $trades = app::get('syslogistics')->rpcCall('trade.get',array('tid'=>$params['tid'],'oid' => $params['oids'],'fields'=>$fields));
        $orders = $trades['orders'];

        $deliveryId = $this->_getDeliveryId($params['tid']);
        $delivery = array(
            'delivery_id' => $deliveryId,
            'tid' => $trades['tid'],
            'user_id' =>$trades['user_id'],
            'shop_id' =>$params['shop_id'],
            'seller_id' =>$params['seller_id'],
            'post_fee' =>$trades['post_fee'],
            'is_protect' => 0,
            'receiver_name' => $trades['receiver_name'],
            'receiver_state'=> $trades['receiver_state'],
            'receiver_city'=> $trades['receiver_city'],
            'receiver_district'=> $trades['receiver_district'],
            'receiver_address'=> $trades['receiver_address'],
            'receiver_zip'=> $trades['receiver_zip'],
            'receiver_mobile'=> $trades['receiver_mobile'],
            'receiver_phone'=> $trades['receiver_phone'],
            't_begin' => time(),
            'status' => 'ready',
        );

        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $objMdlDeliveryDetail = app::get('syslogistics')->model('delivery_detail');
        $db = app::get('syslogistics')->database();
        $db->beginTransaction();
        try{
            $result = $objMdlDelivery->insert($delivery);

            if(!$result)
            {
                throw new \LogicException('发货单创建失败');
            }

            foreach($orders as $order)
            {
                $detail = [];
                $detail['delivery_id'] = $result;
                $detail['oid'] = $order['oid'];
                $detail['item_type'] = "item";
                $detail['sku_id'] = $order['sku_id'];
                $detail['sku_bn'] = $order['bn'];
                $detail['sku_title'] = $order['title'];
                $detail['number'] = $order['num'];
                $isSave = $objMdlDeliveryDetail->save($detail);
                if(!$isSave)
                {
                    throw new \LogicException("发货单明细保存失败");
                }

                //当有赠品时，发货单记录赠品数据
                if($order['gift_data'])
                {
                    $this->__saveGiftDetail($order,$result);
                }
            }
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollback();
            throw $e;
        }
        return $result;
    }

    private function __saveGiftDetail($order,$deliveryId)
    {
        $objMdlDeliveryDetail = app::get('syslogistics')->model('delivery_detail');
        $giftData = $order['gift_data'];
        foreach($giftData as $gift)
        {
            $detail = [];
            $detail['delivery_id'] = $deliveryId;
            $detail['oid'] = $order['oid'];
            $detail['item_type'] = "gift";
            $detail['sku_id'] = $gift['sku_id'];
            $detail['sku_bn'] = $gift['bn'];
            $detail['sku_title'] = $gift['title'];
            $detail['number'] = $gift['gift_num'];
            $isSave = $objMdlDeliveryDetail->save($detail);
            if(!$isSave)
            {
                throw new \LogicException("发货单明细保存失败");
            }
        }
        return true;
    }

    private function _getDeliveryId($tid)
    {
        $sign = '1'.date("Ymd");
        $microtime = microtime(true);
        mt_srand($microtime);
        $randval = substr(mt_rand(), 0, -3) . rand(100, 999);
        return $sign.$randval;
    }
}



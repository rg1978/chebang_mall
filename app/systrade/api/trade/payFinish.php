<?php
class systrade_api_trade_payFinish {

    public $apiDescription = "订单支付状态改变";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单id'],
            'payment' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'已支付金额'],
        );
        return $return;
    }

    public function tradePay($params)
    {
        $tid = $params['tid'];
        $objTrade = kernel::single('systrade_data_trade');
        $tradeInfo = $objTrade->getTradeInfo('payment,status,tid,shop_id,hongbao_fee',['tid'=>$tid]);
        if($tradeInfo['status'] != 'WAIT_BUYER_PAY' )
        {
            logger::info("支付已成功的订单，不需要重复支付");
            return true;
        }

        $db = app::get('systrade')->database();
        $db->beginTransaction();
        $objMdlOrder = app::get('systrade')->model('order');
        try{

            foreach($tradeInfo['order'] as $orderkey=>$orderData)
            {
                $this->__minusStore($orderData);
            }

            $tradeData['data']['status']='WAIT_SELLER_SEND_GOODS';
            $tradeData['data']['modified_time']=time();
            $tradeData['data']['pay_time']=time();
            $tradeData['data']['payed_fee'] = ecmath::number_plus(array($params['payment'], $tradeInfo['hongbao_fee']));
            $tradeData['filter']['tid'] = $tid;

            logger::info("支付成功，更新主订单".var_export($tradeData,1));
            $result = $objTrade->updateTrade($tradeData);
            if(!$result)
            {
                throw new \LogicException("主订单支付状态更新失败");
            }

            $orders = array(
                'status'=>'WAIT_SELLER_SEND_GOODS',
                'pay_time'=> time(),
            );

            logger::info("支付成功，更新子订单".var_export($orders,1));
            if(!$objMdlOrder->update($orders, array('tid'=>$tid) ) )
            {
                $msg = "子订单支付状态修改失败";
                throw new \LogicException($msg);
            }

            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        event::fire('trade.pay', [$tid, $params['payment'], $tradeInfo['shop_id']] );

        return true;
    }

    private function __minusStore($orderData)
    {
        // 处理sku订单冻结
        $params = array(
            'item_id' => $orderData['item_id'],
            'sku_id' => $orderData['sku_id'],
            'quantity' => $orderData['num'],
            'sub_stock' => $orderData['sub_stock'],
            'status' => 'afterpay',
        );
        $isMinus = app::get('systrade')->rpcCall('item.store.minus',$params);
        if( ! $isMinus )
        {
            throw new \LogicException(app::get('systrade')->_('冻结库存失败'));
        }

        if(isset($orderData['gift_data']) && $orderData['gift_data'])
        {
            foreach($orderData['gift_data'] as $key=>$value)
            {
                $params = array(
                    'item_id' => $value['item_id'],
                    'sku_id' => $value['sku_id'],
                    'quantity' => $value['gift_num'],
                    'sub_stock' => $value['sub_stock'],
                    'status' => 'afterpay',
                );
                $isMinus = app::get('systrade')->rpcCall('item.store.minus',$params);
                if( ! $isMinus )
                {
                    throw new \LogicException(app::get('systrade')->_('冻结赠品库存失败'));
                }
            }
        }
        return true;

    }
}



<?php
class systrade_api_trade_moneyAndGoods{
    public $apiDescription = "确认收款、收货";
    public function getParams()
    {
        $data['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单编号'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单所属店铺id'],
            'seller_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单所属店铺的操作员'],
            'seller_name' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单所属店铺的操作员'],
            'memo' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'操作备注'],
        );
        return $data;
    }
    public function receipt($params)
    {
        //获取被操作的订单信息
        $params['fields'] = "user_id,tid,shop_id,status,payment,post_fee,pay_type,receiver_state,receiver_city,receiver_district,receiver_address,receiver_name,receiver_mobile,orders.tid,orders.oid,orders.item_id,orders.sku_id,orders.num,orders.sub_stock";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params);
        try{
            $paymentParams = array(
                'tids' => $tradeInfo['tid'],
                'money' => $tradeInfo['payment'],
                'user_id' => $tradeInfo['user_id'],
                'user_name' => app::get('systrade')->rpcCall('user.get.account.name',['user_id'=>$tradeInfo['user_id']]),
                'seller_id' => $params['seller_id'],
                'seller_name' => $params['seller_name'],
                'memo' => $params['memo'],
                'pay_app_id' => "offline",
            );
            $paymentId = app::get('topc')->rpcCall('payment.trade.payandfinish',$paymentParams);

            //创建货到付款单,并完成支付单
            if(!$paymentId)
            {
                throw new \LogicException('操作失败，支付失败');
            }

            //货到付款时，库存扣减已经再下单时扣减完成，此处不需要再扣减

            //更新订单支付状态，并完成订单
            $objTrade = kernel::single('systrade_data_trade');
            $tradeData['data']['pay_time'] = time();
            $tradeData['data']['payed_fee'] = $tradeInfo['payment'];
            $tradeData['filter']['tid'] = $params['tid'];
            $result = $objTrade->updateTrade($tradeData);

            if(!$result)
            {
                throw new \LogicException("主订单支付状态更新失败");
            }

            $orders = array(
                'pay_time'=>time(),
            );
            $objMdlOrder = app::get('systrade')->model('order');
            if(!$objMdlOrder->update($orders, array('tid'=>$params['tid']) ) )
            {
                $msg = "子订单支付状态修改失败";
                throw new \LogicException($msg);
            }

            //确认收货
            $finishParam = array(
                'tid' => $params['tid'],
                'shop_id' =>$tradeInfo['shop_id'],
            );
            $result = app::get('systrade')->rpcCall('trade.confirm',$finishParam,'seller');
        }
        catch( \Exception $e )
        {
            throw $e;
        }

        event::fire('trade.pay', [$params['tid'], $tradeInfo['payment']] );

        return true;
    }
}



<?php
class ectools_api_payment_pay{
    public $apiDescription = "订单支付请求支付网关";
    public function getParams()
    {
        $return['params'] = array(
            'payment_id' => ['type'=>'string','valid'=>'required', 'description'=>'支付单编号', 'default'=>'', 'example'=>''],
            'pay_app_id' => ['type'=>'string','valid'=>'required_without:hongbao_ids', 'description'=>'支付方式', 'default'=>'', 'example'=>'alipay'],
            'platform' => ['type'=>'string','valid'=>'required', 'description'=>'来源平台（wap、pc）', 'default'=>'pc', 'example'=>'pc'],
            'money' => ['type'=>'string','valid'=>'required', 'description'=>'支付金额', 'default'=>'', 'example'=>'234.50'],
            'deposit_password' => ['type'=>'string','valid'=>'', 'description'=>'预存款支付密码', 'default'=>'', 'example'=>'234.50'],
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'用户id', 'default'=>'', 'example'=>'1'],
            'tids' => ['type'=>'string','valid'=>'required', 'description'=>'被支付的订单号集合,用逗号隔开', 'default'=>'', 'example'=>'1241231213432,2354234523452'],
            'hongbao_ids' => ['type'=>'string','valid'=>'required_without:pay_app_id', 'description'=>'使用支付的红包ID,用逗号隔开', 'default'=>'', 'example'=>'1,2,3'],
            //'itemtype' => ['type'=>'string','valid'=>'', 'description'=>'商品类型', 'default'=>'', 'example'=>''],
        );
        return $return;
    }
    public function doPay($params)
    {
        if(!$params['platform'])
        {
            $params['platform'] = "pc";
        }

        $objMdlPayments = app::get('ectools')->model('payments');
        $objMdlPayBill = app::get('ectools')->model('trade_paybill');
        $paymentBill = $objMdlPayments->getRow('payment_id,status,money,pay_type,currency,cur_money,pay_app_id',array('payment_id'=>$params['payment_id']));

        if($paymentBill['status'] == 'succ' || $paymentBill['status'] == 'progress')
        {
            throw new Exception('该订单已经支付');
        }

        $tradePayBill = $objMdlPayBill->getList('tid',array('payment_id'=>$params['payment_id']));
        $payTids = array_bind_key($tradePayBill,'tid');

        $tids['user_id'] = $params['user_id'];
        $tids['tid'] = $params['tids'];
        $tids['fields'] = "payment,tid,status,order.title,hongbao_fee";
        $trades = app::get('ectools')->rpcCall('trade.get.list',$tids);

        //需要支付的总金额
        $totalMoney = 0;
        foreach( $trades['list'] as $row  )
        {
            $totalMoney = ecmath::number_plus(array($totalMoney, ecmath::number_minus(array($row['payment'], $row['hongbao_fee']))));
        }

        foreach( $trades['list'] as $row  )
        {
            if( $row['status'] != 'WAIT_BUYER_PAY' )
            {
                throw new LogicException('该订单不需要付款，请重新核对付款订单！');
            }
        }

        if( $params['hongbao_ids'] )
        {
            $useHongbaoParams = [
                'user_id'=>$params['user_id'],
                'pay_password'=>$params['deposit_password'],
                'user_hongbao_id'=>$params['hongbao_ids'],
                'tid'=>$params['tids'],
                'used_platform'=>$params['platform'],
            ];
            $hongbaoUseMoney = app::get('ectools')->rpcCall('user.hongbao.use',$useHongbaoParams);

            //红包支付金额等于订单金额
            if( $hongbaoUseMoney['total'] == $totalMoney )
            {
                foreach( $trades['list'] as $row )
                {
                    app::get('ectools')->rpcCall('trade.pay.finish',array('tid'=>$row['tid'],'payment'=>'0'));
                }

                if($params['platform'] == "wap")
                {
                    redirect::action('topwap_ctl_paycenter@finish', array('payment_id'=>$params['payment_id']))->send();
                }
                else
                {
                    redirect::action('topc_ctl_paycenter@finish', array('payment_id'=>$params['payment_id']))->send();
                }
            }

            $params['money'] = ecmath::number_minus(array($totalMoney, $hongbaoUseMoney['total']));
        }
        else
        {
            $params['money'] = $totalMoney;
            if( ! $params['pay_app_id'] )
            {
                throw new LogicException('请选择支付方式');
            }
        }

        $db = app::get('ectools')->database();
        $db->beginTransaction();
        try{
            $return_url = array("topc_ctl_paycenter@finish",array('payment_id'=>$params['payment_id']));
            if($params['platform'] == "wap")
            {
                $return_url = array("topwap_ctl_paycenter@finish",array('payment_id'=>$params['payment_id']));
            }
            $paymentData = array(
                'money' => $params['money'],
                'cur_money' => $params['money'],
                'status' => 'paying',
                'pay_app_id' => $params['pay_app_id'],
                'return_url' => $return_url,
            );

            $paymentBill['money'] = $paymentData['money'];
            $paymentBill['cur_money'] = $paymentData['cur_money'];
            $paymentBill['status'] = $paymentData['status'];
            $paymentBill['pay_app_id'] = $paymentData['pay_app_id'];
            $paymentBill['return_url'] = $paymentData['return_url'];

            $paymentFilter['payment_id'] = $params['payment_id'];
            $result = $objMdlPayments->update($paymentData,$paymentFilter);
            if(!$result)
            {
                throw new Exception('支付失败，支付单更新失败');
            }

            foreach($trades['list'] as $val)
            {
                //红包支付分摊到订单的金额
                $tradeHongbaoPayment = 0;
                $tradeHongbaoPayment = $hongbaoUseMoney['trade'][$val['tid']]['payment'];
                $tradeHongbaoPayment = $tradeHongbaoPayment ?: $val['hongbao_fee'];
                $data['payment'] = ecmath::number_minus(array($val['payment'], $tradeHongbaoPayment));
                $data['status'] = 'paying';
                $data['modified_time'] = time();
                $filter['tid'] = $val['tid'];
                $filter['payment_id'] = $params['payment_id'];
                $result = $objMdlPayBill->update($data,$filter);
                $params['item_title'][] = $val['order'][0]['title'];
                if(!$result)
                {
                    throw new Exception('支付失败，支付单明细更新失败');
                }
                if($payTids[$val['tid']])
                {
                    unset($payTids[$val['tid']]);
                }
            }

            if($payTids)
            {
                $deleteParams['tid'] = array_keys($payTids);
                $deleteParams['payment_id'] = $params['payment_id'];
                $result = $objMdlPayBill->delete($deleteParams);
                if(!$result)
                {
                    throw new Exception('支付失败，清除过期数据失败');
                }
            }

            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        $paymentBill['pay_app_id'] = $params['pay_app_id'];
        $paymentBill['item_title'] = $params['item_title'][0];
        $paymentBill['deposit_password'] = $params['deposit_password'];
        $objPayment = kernel::single('ectools_pay');
        $result = $objPayment->generate($paymentBill);
        if(!$result)
        {
            throw new Exception('支付失败,请求支付网关出错');
        }
        return true;
    }
}



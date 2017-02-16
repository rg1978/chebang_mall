<?php
class topc_ctl_paycenter extends topc_controller{

    public function __construct($app)
    {
        parent::__construct();
        $this->setLayoutFlag('paycenter');
        // 检测是否登录
    }

    public function index()
    {
        $filter = input::get();
        if(isset($filter['tid']) && $filter['tid'])
        {
            $pagedata['payment_type'] = "offline";
            $ordersMoney = app::get('topc')->rpcCall('trade.money.get',array('tid'=>$filter['tid']),'buyer');

            if($ordersMoney)
            {
                foreach($ordersMoney as $key=>$val)
                {
                    $newOrders[$val['tid']] = $val['payment'];
                    $newMoney += $val['payment'];
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
            }

            $pagedata['trades'] = $paymentBill;
            $pagedata['payment_type'] = "offline";
            $pagedata['mainfile'] = "topc/payment/payment.html";
            return $this->page('topc/payment/index.html', $pagedata);
        }

        if($filter['newtrade'])
        {
            $newtrade = $filter['newtrade'];
            unset($filter['newtrade']);
        }

        if($filter['merge'])
        {
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        //获取可用的支付方式列表
        $filter['fields'] = "*";
        $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$filter,'buyer');
        if($paymentBill['status'] == "succ")
        {
            return $this->finish(['payment_id'=>$paymentBill['payment_id']]);
        }

        $apiParams = [
            'user_id' => userAuth::id(),
            'is_valid'=> 'viable',
            'fields'=>'hongbao_id,name,money,id,end_time',
            'page_size'=>'100',
            'used_platform' => "pc",
        ];
        $hongbaoData = app::get('topc')->rpcCall('user.hongbao.list.get', $apiParams);
        $pagedata['hongbao_list'] = $hongbaoData['list'];

        // 获取当前平台设置的货币符号和精度
        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $pagedata['cur_symbol'] = $cur_symbol;

        //检测订单中的金额是否和支付金额一致 及更新支付金额
        $trade = $paymentBill['trade'];
        $tids['tid'] = implode(',',array_keys($trade));
        if(empty($tids['tid']))
            return kernel::abort(404);
        $ordersMoney = app::get('topc')->rpcCall('trade.money.get',$tids,'buyer');

        if($ordersMoney)
        {
            $newMoney = 0;
            foreach($ordersMoney as $key=>$val)
            {
                $newOrders[$val['tid']] = $val['payment'];
                $newMoney = ecmath::number_plus(array($newMoney, ecmath::number_minus(array($val['payment'], $val['hongbao_fee']))));
            }

            //如果需要支付的金额为0，跳转到订单列表
            if( $newMoney <= 0 )
            {
                redirect::action('topc_ctl_member_trade@tradeList')->send();
            }

            $result = array(
                'trade_own_money' => json_encode($newOrders),
                'money' => $newMoney,
                'cur_money' => $newMoney,
                'payment_id' => $filter['payment_id'],
            );

            if($newMoney != $paymentBill['cur_money'])
            {
                try{
                    app::get('topc')->rpcCall('payment.money.update',$result);
                }
                catch(Exception $e)
                {
                    $msg = $e->getMessage();
                    $url = url::action('topc_ctl_member_trade@tradeList');
                    return $this->splash('error',$url,$msg,true);
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
            }
        }

        $payType['platform'] = 'ispc';
        $payments = app::get('topc')->rpcCall('payment.get.list',$payType,'buyer');
        $payments = $this->paymentsSort($payments,'app_order_by');

        $pagedata['tids'] = $tids['tid'];
        $pagedata['trades'] = $paymentBill;
        $pagedata['payments'] = $payments;
        $pagedata['newtrade'] = $newtrade;
        $pagedata['mainfile'] = "topc/payment/payment.html";
        $pagedata['hasDepositPassword'] = app::get('topc')->rpcCall('user.deposit.password.has', ['user_id'=>userAuth::id()]);
        return $this->page('topc/payment/index.html', $pagedata);
    }

    public function createPay()
    {
        $filter = input::get();
        $filter['user_id'] = userAuth::id();
        $filter['user_name'] = userAuth::getLoginName();
        if($filter['merge'])
        {
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        try
        {
            $paymentId = kernel::single('topc_payment')->getPaymentId($filter);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topc_ctl_member_trade@tradeList');
            echo '<meta charset="utf-8"><script>alert("'.$msg.'");location.href="'.$url.'";</script>';
            exit;
        }
        $url = url::action('topc_ctl_paycenter@index',array('payment_id'=>$paymentId,'merge'=>$ifmerge));
        return $this->splash('success',$url,$msg,true);
    }

    public function dopayment()
    {
        $postdata = input::get();
        $payment = $postdata['payment'];
        $payment['deposit_password'] = $postdata['deposit_password'];
        $payment['user_id'] = userAuth::id();
        $payment['platform'] = "pc";
        $payment['hongbao_ids'] = implode(',',$postdata['user_hongbao_id']);
        try
        {
            app::get('topc')->rpcCall('payment.trade.pay',$payment);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $code = $e->getCode();
            return $this->errorPay($payment['payment_id'], $msg, '', $code);
        }
        $url = url::action('topc_ctl_paycenter@finish',array('payment_id'=>$payment['payment_id']));
        return $this->splash('success',$url,$msg,true);
    }

    //用来确认支付单是否支付成功
    public function checkPayments()
    {
        $postdata = input::get();
        if(!is_numeric($postdata['payment_id']))
        {
            $this->splash('failed',null,"payment_id格式错误",true);exit;
        }
        $params['payment_id'] = $postdata['payment_id'];
        $result = app::get('topc')->rpcCall('payment.checkpayment.statu',$params);
        return $result;
    }

    public function finish($postdata = array())
    {
        if(!$postdata)
        {
            $postdata = input::get();
        }

        try
        {
            $params['payment_id'] = $postdata['payment_id'];
            $params['fields'] = 'payment_id,status,pay_app_id,pay_name,money,cur_money,return_url';
            $result = app::get('topc')->rpcCall('payment.bill.get',$params);

            // 支付结果处理，主要是处理预存款充值
            if(strpos($result['return_url'], 'topc_ctl_member_deposit@rechargeResult'))
            {
                $returnParams = unserialize($result['return_url']);
                return redirect::action('topc_ctl_member_deposit@rechargeResult', ['payment_id'=>$returnParams[1]['payment_id']]);
            }

            $apiParams['user_id'] = userAuth::id();;
            $apiParams['tid'] = implode(",",array_column($result['trade'], 'tid'));
            $apiParams['fields'] = "tid,payment,payed_fee,hongbao_fee,status,pay_type";
            $trades = app::get('topc')->rpcCall('trade.get.list',$apiParams);

            $hongbaoMoney = 0;
            $tradeTotalPayment = 0;
            foreach( $trades['list'] as $row )
            {
                $hongbaoMoney = ecmath::number_plus(array($hongbaoMoney, $row['hongbao_fee']));
                $tradeTotalPayment = ecmath::number_plus(array($tradeTotalPayment, $row['payment']));
            }
            $pagedata['hongbao_fee'] = $hongbaoMoney;

            //查看订单付款状态并做出判断
            // $params['payment_id'] = $postdata['payment_id'];
            $payStatus = app::get('topc')->rpcCall('payment.checkpayment.statu', array('payment_id'=>$postdata['payment_id']));

            if($hongbaoMoney != $tradeTotalPayment )
            {
                if( $payStatus !='succ' )
                {
                    $msg = '订单支付失败，请重试';
                    return $this->errorPay($postdata['payment_id'], $msg, $payStatus);
                }
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }

        $result['num'] = count($result['trade']);
        $pagedata['msg'] = $msg;
        $pagedata['payment'] = $result;
        $pagedata['mainfile'] = "topc/payment/finish.html";
        return $this->page('topc/payment/index.html', $pagedata);
    }


    /**
     *  订单错误页面提示
     *  @param int $paymentId
     *  @param string $msg
     *  @param string $result
     *  @return void
     * */
    public function errorPay($paymentId, $msg = '', $result='', $code=0)
    {
        $postdata = input::get();
        if($postdata['payment_id'])
        {
            $paymentId = $postdata['payment_id'];
        }
        if(!$paymentId)
        {
            kernel::abort('404');
        }
        $params['payment_id'] = $paymentId;

        $notice = '订单支付失败，请重试';
        $msg = $msg ? $msg : $notice;
        $pagedata = array();

        //status表示订单是否存在
        $pagedata['status'] = true;
        $pagedata['msg'] = $msg;

        //判断订单状态
        if(!$result)
        {
            $result = app::get('topc')->rpcCall('payment.checkpayment.statu',$params);
            if(!$result)
            {
                $pagedata['msg'] = '订单不存在';
                $pagedata['status'] = false;
                return $this->page('topc/payment/error.html', $pagedata);
            }
        }

        if( $result !='succ')
        {
            //获取订单详情
            $params['fields'] = 'cur_money';
            $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$params);
            $trade = $paymentBill['trade'];
            $tids = array_keys($trade);
            $iparams['tid'] = $tids;
            $iparams['user_id'] = userAuth::id();
            $iparams['fields'] = "tid,orders.title";
            $itrade = app::get('topc')->rpcCall('trade.get',$iparams);
            $orders = $itrade['orders'];
            $pagedata['cur_money'] = $paymentBill['cur_money'];
            $pagedata['orders'] = $orders;
            $pagedata['payment_id'] = $paymentId;
            if($code && strpos($code, userAuth::id()) !== false)
            {
                $pagedata['depositNotEnough'] = true;
            }

            return $this->page('topc/payment/error.html', $pagedata);
        }else
        {
            return redirect::action('topc_ctl_paycenter@finish', array('payment_id' => $params['payment_id']));
        }

    }

    //支付方式排序
    public function paymentsSort($payments,$orderBy,$sort_order=SORT_ASC)
    {
        if(is_array($payments)){
            foreach ($payments as $value) {
                if(is_array($value)){
                    $paymentList[] = $value[$orderBy];
                }
            }
        }
        array_multisort($paymentList,$sort_order,$payments);
        return $payments;
    }
}



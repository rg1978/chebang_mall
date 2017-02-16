<?php

class systrade_api_getTradeInfoByShop {

    /**
     * 接口作用说明
     */
    public $apiDescription = '(商家)获取单笔交易信息';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'18','description'=>'店铺ID'],
            'tid' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单编号'],
            'oid' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'子订单编号，返回指定子订单编号的orders数据结构'],
            'fields'=> ['type'=>'field_list','valid'=>'required', 'default'=>'', 'example'=>'*,orders.*,buyerInfo.*', 'description'=>'获取单个订单需要返回的字段'],
        );

        //如果参数fields中存在orders，则表示需要获取子订单的数据结构
        $return['extendsFields'] = ['orders','buyerInfo','payments'];

        return $return;
    }

    /**
     * 获取单笔交易数据
     *
     * @param array $params 接口传入参数
     * @return array
     */
    public function getData($params)
    {
        $filter['shop_id'] = $params['shop_id'];

        if( $params['oid'] )
        {
            $params['oid'] = explode(',',$params['oid']);
        }

        $tradeInfo = kernel::single('systrade_getTradeData')->getTradeInfo($params['fields'], $params['tid'], $params['oid'], $filter);
        if( empty($tradeInfo) ) return [];

        $shippingType = [
            'express' => '快递',
            'ziti' => '门店自提',
            'post' => '平邮',
            'ems' => 'EMS',
            'virtual' => '虚拟发货',
        ];

        $tradeInfo['dlytmpl_name'] = $shippingType[$tradeInfo['shipping_type']];
        if( $tradeInfo['shipping_type'] == 'ziti' )
        {
            $tradeInfo['receiver_address'] = $tradeInfo['ziti_addr'];
        }

        //这里判断货到付款
        //现在判断货到付款在订单里没有字段标示，所以采用两个字段相结合，就是支付为货到付款且订单状态是待支付的时候，就判断为货到付款
        $tradeInfo['is_cod'] = ($tradeInfo['pay_type'] == "offline") ? 'true' : 'false';

        if( $tradeInfo['user_id'] )
        {
            $userInfo = app::get('topshop')->rpcCall('user.get.info', ['user_id' => $tradeInfo['user_id']]);
            $tradeInfo['buyerInfo']['uname'] = $userInfo['login_account'];
            $tradeInfo['buyerInfo']['email'] = $userInfo['email'];
            $tradeInfo['buyerInfo']['mobile'] = $userInfo['mobile'];
            $tradeInfo['buyerInfo']['username'] = $userInfo['username'];
        }

        if( $tradeInfo['status'] != 'WAIT_BUYER_PAY' && $params['fields']['extends']['payments'] )
        {
           $paymentBillParams = [
               'tids'=>$params['tid'],
               'status'=>'succ',
               'fields' => 'currency,bank,pay_account,account,pay_type,pay_app_id,pay_name,payed_time,user_name,pay_app_id',
           ];
           $paymentsData = app::get('systrade')->rpcCall('payment.bill.get', $paymentBillParams);
           if( $paymentsData )
           {
               foreach( $paymentsData['trade'] as $paymentBill )
               {
                   $billId = $paymentBill['paybill_id'];
                   $tradeInfo['payments'][] = [
                       'currency' => $paymentsData['currency'] ? $paymentsData['currency'] : "CNY",
                       'bank' => $paymentsData['bank'],
                       'account' => $paymentsData['account'],
                       'user_name' => $paymentsData['user_name'],
                       'pay_name' => $paymentsData['pay_name'],
                       'pay_app_id' => $paymentsData['pay_app_id'],
                       'pay_account' => $paymentsData['pay_account'],
                       'payment_id' => $paymentBill['payment_id'],
                       'paybill_id' => $billId,
                       'payment' => $paymentBill['payment'],
                       'payed_time' => $paymentBill['payed_time'] ? $paymentBill['payed_time'] : $tradeInfo['pay_time'],
                   ];
               }
           }
        }

        $tradeInfo = $this->__paramsToString($tradeInfo);

        $tradeInfo = $this->__getPayStatus($tradeInfo);

        return $tradeInfo;
    }

    private function __paramsToString($from)
    {
        $to = array();
        if( is_array($from) )
        {
            foreach($from as $k=>$v)
            {
                $to[$k] = $this->__paramsToString($v);
            }
        }
        else
        {
            return (string)$from;
        }
        return $to;
    }

    private function __getPayStatus($tradeInfo)
    {
        if( ! isset($tradeInfo['status']) || ! isset($tradeInfo['is_cod']) || ! isset($tradeInfo['cancel_status']) )
        {
            return $tradeInfo;
        }

        if( $tradeInfo['status'] == 'WAIT_BUYER_PAY'
            || ($tradeInfo['is_cod'] == 'true' && $tradeInfo['status'] != 'TRADE_FINISHED' )
            || $tradeInfo['payed_fee'] <= 0 )
        {
            $tradeInfo['pay_status'] = 'PAY_NO';
        }
        else
        {
            $tradeInfo['pay_status'] = 'PAY_FINISH';
        }

        if( $tradeInfo['pay_status'] == 'PAY_FINISH' )
        {
            if( in_array($tradeInfo['cancel_status'], ['WAIT_PROCESS', 'REFUND_PROCESS'] ) )
            {
                $tradeInfo['pay_status'] = 'REFUNDING';
            }
            elseif( $tradeInfo['cancel_status'] == 'SUCCESS' )
            {
                $tradeInfo['pay_status'] = 'REFUND_ALL';
            }
        }

        //不在进行退款 才会判断是否有售后
        if( ! in_array($tradeInfo['pay_status'],['REFUND_ALL','REFUNDING']) )
        {
            $refundFee = 0;
            foreach( $tradeInfo['orders'] as $orderData )
            {
                if( $orderData['aftersales_status'] == 'SUCCESS' && $tradeInfo['pay_status'] != 'REFUND_PART')
                {
                    $tradeInfo['pay_status'] = 'REFUND_ALL';
                }
                elseif( $tradeInfo['pay_status'] == 'REFUND_ALL' )
                {
                    $tradeInfo['pay_status'] = 'REFUND_PART';
                }

                if( $orderData['aftersales_status'] == 'REFUNDING' )
                {
                    $tradeInfo['pay_status'] = 'REFUNDING';
                    break;
                }

                $refundFee += $orderData['refund_fee'];
            }

            if( $tradeInfo['pay_status'] != 'REFUNDING' && $refundFee && $refundFee < $tradeInfo['payed_fee'] )
            {
                $tradeInfo['pay_status'] = 'REFUND_PART';
            }
        }

        return $tradeInfo;
    }
}

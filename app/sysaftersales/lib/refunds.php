<?php
/**
 *  处理退款
 */
class sysaftersales_refunds {

    public function __construct()
    {
        $this->objMdlRefunds = app::get('sysaftersales')->model('refunds');
    }

    /**
     * 取消已付款的订单，创建退款申请单
     *
     * @param $tid  取消的订单ID
     * @param $status 取消的状态 用户申请取消订单 未审核，商家取消订单，平台取消订单
     * @param $refundsReason 取消订单原因
     * @param $shopId 取消订单的店铺ID
     * @param $refundBn 退款申请单编号
     * @param $returnFreight 取消订单，是否退还运费
     */
    public function cancelRefundApply($tid, $status, $refundsReason, $shopId, $refundBn,$returnFreight=null)
    {
        $params = ['tid' => $tid,'fields' =>'tid,status,payed_fee,post_fee,user_id,shop_id,points_fee,consume_point_fee,hongbao_fee,hongbao_fee,user_hongbao_id'];
        $tradeData = app::get('sysaftersales')->rpcCall('trade.get', $params);
        if( $tradeData && $shopId && $tradeData['shop_id'] != $shopId )
        {
            throw new \Exception("参数错误");
        }

        if( $tradeData['status'] == 'WAIT_BUYER_PAY' )
        {
            throw new \Exception("未付款订单不需要退款");
        }
        elseif( in_array($tradeData['status'], ['TRADE_CLOSED','TRADE_CLOSED_BY_SYSTEM']) )
        {
            throw new \Exception("订单已取消，不需要重复取消");
        }
        //如果不是已付款未发货，则表示订单已发货或者已完成 都不能进行取消订单操作
        //商家强制关单和平台强制关单,则发货的可以取消
        elseif( ! in_array($status,['5','6']) && $tradeData['status'] != 'WAIT_SELLER_SEND_GOODS' )
        {
            throw new \Exception("已发货订单不能直接退款");
        }

        $insertData['refund_bn'] = $refundBn ? $refundBn : $this->getRefundBn();
        $insertData['user_id'] = $tradeData['user_id'];
        $insertData['shop_id'] = $shopId;
        $insertData['tid'] = $tid;
        $insertData['refunds_type'] = '1';
        $insertData['status'] = $status;
        $insertData['refunds_reason'] = $refundsReason;
        $insertData['refund_fee'] = $tradeData['payed_fee'];
        $insertData['total_price'] = $tradeData['payed_fee']+$tradeData['points_fee'];
        $insertData['order_price'] = $tradeData['payed_fee']+$tradeData['points_fee'];
        $insertData['points_fee'] = $tradeData['points_fee'];
        $insertData['user_hongbao_id'] = $tradeData['user_hongbao_id'];
        $insertData['consume_point_fee'] = $tradeData['consume_point_fee'];
        $insertData['created_time'] = time();
        $insertData['modified_time'] = time();
        $insertData['return_freight'] = '2';

        if($tradeData['status'] == "WAIT_BUYER_CONFIRM_GOODS")
        {
            if($returnFreight == "false")
            {
                $insertData['refund_fee'] = $tradeData['payed_fee']-$tradeData['post_fee'];
                $insertData['total_price'] = $insertData['total_price']-$tradeData['post_fee'];
                $insertData['return_freight'] = '3';
            }
            $insertData['refunds_type'] = '2';
        }

        if( $insertData['refund_fee'] <= $tradeData['hongbao_fee'] )
        {
            $insertData['hongbao_fee'] = $insertData['refund_fee'];
        }
        else
        {
            $insertData['hongbao_fee'] = $tradeData['hongbao_fee'];
        }

        $refundsId = $this->objMdlRefunds->insert($insertData);

        //如果是商家取消订单或者平台取消订单，并且红包全额支付，则直接退款成功，退还红包
        if( in_array($status,['5','6']) )
        {
            if( $insertData['hongbao_fee'] == $insertData['refund_fee'] )
            {
                app::get('sysaftersales')->rpcCall('aftersales.refunds.restore', array('refunds_id'=>$refundsId, 'return_fee'=>$insertData['total_price'] ));
                $insertData['is_restore'] = true;
            }
        }

        return $insertData;
    }

    //创建退款申请单编号
    public function getRefundBn()
    {
        $sign = '2'.date("Ymd");
        $microtime = microtime(true);
        mt_srand($microtime);
        $randval = substr(mt_rand(), 0, -3) . rand(100, 999);
        return $sign.$randval;
    }

    /**
     * 创建退款申请单，商家在需要进行退款处理的时候需要向平台发起退款申请，又平台进行退款处理
     *
     * @param array $data 申请退款数据
     * @param int   $tid  订单编号
     * @param int   $oid  子订单编号
     */
    public function afsRefundApply($data, $tid, $oid, $refundBn)
    {
        $params = ['tid' => $tid, 'fields' =>'tid,post_fee,payment,payed_fee,points_fee,orders.payment,orders.points_fee,orders.consume_point_fee,user_id,orders.aftersales_status,hongbao_fee,user_hongbao_id'];
        $tradeData = app::get('sysaftersales')->rpcCall('trade.get', $params);
        foreach(  $tradeData['orders'] as $row )
        {
            if( $row['oid'] == $oid )
            {
                $orderData = $row;
                break;
            }
        }

        $tradeHongbao = 0;

        if( $tradeData['hongbao_fee'] > 0 )
        {
            //获取订单退还的总红包金额
            $refundList = $this->objMdlRefunds->getList('hongbao_fee,status', array('tid'=>$tid));
            if( $refundList )
            {
                $refundHongbaoFee = 0;
                foreach( $refundList as $refundRow )
                {
                    if( $refundRow['status'] != '2' && $refundRow['hongbao_fee'] > 0 )
                    {
                        $refundHongbaoFee = ecmath::number_plus(array($refundHongbaoFee, $refundRow['hongbao_fee']));
                    }
                }
            }

            //订单剩余退还红包的金额
            $tradeHongbao = ecmath::number_minus(array($tradeData['hongbao_fee'], $refundHongbaoFee));
        }

        $paymentTotal = $orderData['payment'];

        //开发者模式下目前不判断退款金额 只联通自营的情况下
        $requestParams = ['shop_id'=>$data['shop_id']];
        $shopConf = app::get('topshop')->rpcCall('open.shop.develop.conf', $requestParams);

        if( $shopConf['develop_mode'] != 'DEVELOP' &&  $data['total_price'] > ecmath::number_minus([$paymentTotal, $orderData['refund_fee']]) )
        {
            throw new \LogicException(app::get('sysaftersales')->_('商品退款金额不能大于付款金额'));
        }

        if($data['total_price'] <= $orderData['points_fee'])
        {
            throw new \LogicException(app::get('sysaftersales')->_('商品退款金额必须大于'.$orderData['points_fee']));
        }

        $total_price =  ecmath::number_minus([$data['total_price'], $orderData['points_fee'] ]);

        if( $total_price <= $tradeHongbao)
        {
            $tradeHongbao = $total_price;
        }

        $insertData['refund_bn'] = $refundBn ? $refundBn : $this->getRefundBn();
        $insertData['aftersales_bn'] = $data['aftersales_bn'];
        $insertData['refunds_reason'] = $data['reason'];
        $insertData['total_price'] =  $data['total_price'];
        $insertData['refund_fee'] = $total_price;
        $insertData['order_price'] =  $orderData['payment'];
        $insertData['points_fee'] =  $orderData['points_fee'];
        $insertData['hongbao_fee'] = $tradeHongbao;
        $insertData['user_hongbao_id'] = $tradeData['user_hongbao_id'];
        $insertData['consume_point_fee'] =  $orderData['consume_point_fee'];
        $insertData['status'] =  '3';//商家同意退款，则表示商家审核通过
        $insertData['tid'] =  $data['tid'];
        $insertData['refunds_type'] = '0';
        $insertData['oid'] =  $oid;
        $insertData['shop_id'] =  $data['shop_id'];
        $insertData['user_id'] =  $tradeData['user_id'];
        $insertData['created_time'] = time();
        $insertData['modified_time'] = time();

        $refundsId = $this->objMdlRefunds->insert($insertData);

        if( $total_price == $tradeHongbao )
        {
            app::get('sysaftersales')->rpcCall('aftersales.refunds.restore', array('refunds_id'=>$refundsId, 'return_fee'=>$data['total_price']));
            $insertData['is_restore'] = true;
        }

        return $insertData;
    }

    public function updateStatus($aftersalesBn,$status)
    {
        return $this->objMdlRefunds->update(array('status'=>$status), array('aftersales_bn'=>$aftersalesBn));
    }
}


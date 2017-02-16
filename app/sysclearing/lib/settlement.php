<?php

/**
 * @brief 商品数据处理
 */
class sysclearing_settlement{

    /**
     *确认收货的时候处理结算明细
     *
     * @param array $tradeInfo 订单数据
     * @param int $settlementType 结算类型 1 为普通结算，2 运费结算，3 退款结算
     */
    function generate($tradeInfo, $settlementType='1')
    {
        //拒收订单处理退款时，生成结算单明细
        if($settlementType == '4')
        {
            return $this->__rejectionTrade($tradeInfo);
        }

        if($tradeInfo && is_array($tradeInfo['orders']))
        {
            $objMdlSettleDetail = app::get('sysclearing')->model('settlement_detail');
            $objLibMath = kernel::single('ectools_math');
            foreach($tradeInfo['orders'] as $key => $val)
            {
                $data = array(
                    'oid' => $val['oid'],
                    'tid' => $val['tid'],
                    'shop_id' => $val['shop_id'],
                    'settlement_time' => time(),
                    'pay_time' => $tradeInfo['pay_time'],
                    'item_id' => $val['item_id'],
                    'sku_id' => $val['sku_id'],
                    'bn' => $val['bn'],
                    'title' => $val['title'],
                    'spec_nature_info' => $val['spec_nature_info'],
                    'price' => $val['price'],
                    'num' => $val['num'],
                    'sku_properties_name' => $val['sku_properties_name'],
                    'divide_order_fee' => $val['divide_order_fee'],
                    'part_mjz_discount' => $val['part_mjz_discount'],
                    'payment' => $val['payment'],
                    'refund_fee' => $val['refund_fee'],
                    'cat_service_rate' => $val['cat_service_rate'],
                    'settlement_type' => $settlementType,
                    'discount_fee' => $val['discount_fee'],
                    'adjust_fee' => $val['adjust_fee'],
                );
                $data['post_fee'] = 0;
                if( $settlementType == '3' )//如果子订单有部分售后退款的情况，需要改造此处
                {
                    //平台提取的佣金返还
                    $commissionFee = $objLibMath->number_multiple(array($val['refund_fee'],$val['cat_service_rate']));
                    $data['commission_fee'] = -$commissionFee;
                    //返还结算给商家的金额
                    $settlementFee = $objLibMath->number_minus(array($val['refund_fee'],$commissionFee));
                    $data['settlement_fee'] = -$settlementFee;
                    //退款订单的商品款为0
                    $data['payment'] = 0;
                }
                else
                {
                    if($key == '0')//将运费赋值到第一条子订单中
                    {
                        $data['post_fee'] = $tradeInfo['post_fee'] ? $tradeInfo['post_fee'] : 0;
                    }
                    //计算用户最终实际付款的金额，付款金额-退款金额
                    $payment = $objLibMath->number_minus(array($val['payment'],$val['refund_fee']));
                    //计算平台提取的佣金
                    $data['commission_fee'] = $objLibMath->number_multiple(array($payment,$val['cat_service_rate']));
                    //计算结算给商家的金额
                    $data['settlement_fee'] = $objLibMath->number_minus(array($val['payment'],$data['commission_fee']));
                }

                if(!$objMdlSettleDetail->save($data))
                {
                    return false;
                }

                if($val['gift_data'])
                {
                    $this->__saveGiftData($val,$settlementType);
                }
            }
        }

        return true;
    }

    private function __saveGiftData($order,$settlementType)
    {
        $giftdata = $order['gift_data'];
        $objMdlSettleDetail = app::get('sysclearing')->model('settlement_detail');
        foreach($giftdata as $key=>$gift)
        {

            $data = array(
                'oid' => $order['oid'],
                'tid' => $order['tid'],
                'shop_id' => $order['shop_id'],
                'settlement_time' => time(),
                'pay_time' => $order['pay_time'],
                'item_id' => $gift['item_id'],
                'sku_id' => $gift['sku_id'],
                'bn' => $gift['bn'],
                'title' => '【赠品】'.$gift['title'],
                'spec_nature_info' => $gift['spec_info'],
                'price' => 0,
                'num' => $gift['gift_num'],
                'sku_properties_name' => $order['sku_properties_name'],
                'divide_order_fee' => $order['divide_order_fee'],
                'part_mjz_discount' => $order['part_mjz_discount'],
                'payment' => 0,
                'refund_fee' => 0,
                'cat_service_rate' => 0,
                'settlement_type' => $settlementType,
                'discount_fee' => 0,
                'adjust_fee' => 0,
                'commission_fee' =>0,
                'settlement_fee' =>0,
                'post_fee' => 0,
            );

            if(!$objMdlSettleDetail->save($data))
            {
                return false;
            }

        }
        return true;
    }

    public function doConfirm($settlementNo, $status)
    {
        if($status=='2')
        {
            $status = '2';
        }
        else
        {
            return fase;
        }
        return app::get('sysclearing')->model('settlement')->update(array('settlement_status'=>$status),array('settlement_no'=>$settlementNo));
    }

    private function __rejectionTrade($tradeInfo)
    {
        $settlementType = 4;
        //获取该订单的拒收退款申请单
        $params = array(
            'fields' => 'refunds_type,return_freight,total_price',
            'tid' =>$tradeInfo['tid'],
            'status' => 1,
        );
       $refunds = app::get('sysclearing')->rpcCall('aftersales.refundapply.list.get',$params);
       $refunds = $refunds['list'][0];
        $objMdlSettleDetail = app::get('sysclearing')->model('settlement_detail');
        $objLibMath = kernel::single('ectools_math');
        foreach($tradeInfo['orders'] as $key => $val)
        {
            $data = array(
                'oid' => $val['oid'],
                'tid' => $val['tid'],
                'shop_id' => $val['shop_id'],
                'settlement_time' => time(),
                'pay_time' => $tradeInfo['pay_time'],
                'item_id' => $val['item_id'],
                'sku_id' => $val['sku_id'],
                'bn' => $val['bn'],
                'title' => $val['title'],
                'spec_nature_info' => $val['spec_nature_info'],
                'price' => $val['price'],
                'num' => $val['num'],
                'sku_properties_name' => $val['sku_properties_name'],
                'divide_order_fee' => $val['divide_order_fee'],
                'part_mjz_discount' => $val['part_mjz_discount'],
                'payment' => $val['payment'],
                'refund_fee' => $val['payment'],
                'cat_service_rate' => $val['cat_service_rate'],
                'settlement_type' => $settlementType,
                'discount_fee' => $val['discount_fee'],
                'adjust_fee' => $val['adjust_fee'],
            );
            $data['post_fee'] = 0 ;
            if($key == '0' && $refunds['refunds_type'] == '2' && $refunds['return_freight'] == '2') //拒收订单退运费
            {
                $data['post_fee'] = -$tradeInfo['post_fee'] ;
            }

            //计算用户最终实际付款的金额，付款金额-退款金额
            $payment = $val['payment'];
            //退款明细单的商品款的金额为0
            $data['payment'] = 0;

            //平台提取的佣金返还
            $commissionFee = $objLibMath->number_multiple(array($payment,$val['cat_service_rate']));
            $data['commission_fee'] = -$commissionFee;
            //返还结算给商家的金额
            $settlementFee = $objLibMath->number_minus(array($payment,$commissionFee));
            $data['settlement_fee'] = -$settlementFee;

            if(!$objMdlSettleDetail->save($data))
            {
                return false;
            }

            if($val['gift_data'])
            {
                $this->__saveGiftData($val,$settlementType);
            }
        }
        return true;
    }
}


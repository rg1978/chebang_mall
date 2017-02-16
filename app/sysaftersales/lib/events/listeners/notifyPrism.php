<?php
/**
 * 订单事件触发将消息通知到prism
 *
 */
class sysaftersales_events_listeners_notifyPrism implements base_events_interface_queue
{

    private function __check($shopId)
    {
        $requestParams = ['shop_id'=>$shopId];
        $shopConf = app::get('systrade')->rpcCall('open.shop.develop.conf', $requestParams);
        if( $shopConf['develop_mode'] != 'DEVELOP' ) return false;

        return true;
    }

    /**
     * 创建退款申请消息，将消息通知到prism
     *
     * @param array $tid 退款的订单
     * @param array $data
     */
    public function refundCreated($tid, $data)
    {
        if( !$this->__check($data['shop_id'])  ) return true;

        $notifyData['prismNotifyName'] = 'refundCreated';
        $notifyData['tid'] = $tid;

        if( $data['oid'] )//取消订单生成退款单不需要子订单号，售后退款需要指定子订单
        {
            $notifyData['oid'] = $data['oid'];
        }

        $notifyData['shop_id'] = $data['shop_id'];
        $notifyData['user_id'] = $data['user_id'];
        $notifyData['refund_bn'] = $data['refund_bn'];
        $notifyData['refunds_id'] = $data['refunds_id'];
        $notifyData['refunds_type'] = $data['refunds_type'];//0 售后申请退款 1 取消订单退款
        $notifyData['refund_fee'] = $data['total_price'];

        return kernel::single('system_prism_notify')->write($notifyData['shop_id'], $notifyData);
    }

    /**
     * 更新退款申请单
     */
    public function refundModified($data)
    {
        if( !$this->__check($data['shop_id'])  ) return true;

        $notifyData['prismNotifyName'] = 'refundModified';
        $notifyData['tid'] = $data['tid'];

        if( $data['oid'] )//取消订单生成退款单不需要子订单号，售后退款需要指定子订单
        {
            $notifyData['oid'] = $data['oid'];
        }

        $notifyData['shop_id'] = $data['shop_id'];
        $notifyData['user_id'] = $data['user_id'];
        $notifyData['refund_bn'] = $data['refund_bn'];
        $notifyData['refunds_id'] = $data['refunds_id'];
        $notifyData['refunds_type'] = $data['refunds_type'];//0 售后申请退款 1 取消订单退款
        $notifyData['refund_fee'] = $data['total_price'];
        $notifyData['status'] = $data['status'];

        return kernel::single('system_prism_notify')->write($notifyData['shop_id'], $notifyData);
    }

    /**
     * 创建售后申请单消息
     *
     * @param $data 创建售后申请单消息体
     * @param $shopId 该售后申请单所属店铺
     */
    public function afterSalesCreated($data, $shopId)
    {

        if( !$this->__check($shopId) ) return true;

        $notifyData['prismNotifyName'] = 'afterSalesCreated';
        $notifyData['aftersales_bn'] = $data['aftersales_bn'];
        $notifyData['aftersales_type'] = $data['aftersales_type'];
        $notifyData['tid'] = $data['tid'];
        $notifyData['oid'] = $data['oid'];
        $notifyData['shop_id'] = $shopId;
        $notifyData['user_id'] = $data['user_id'];

        return kernel::single('system_prism_notify')->write($shopId, $notifyData);
    }

    /**
     * 更新售后单状态
     */
    public function afterSalesUpdateStatus($data, $shopId)
    {
        //判断消息是否需要推送到prism
        if( !$this->__check($shopId) ) return true;

        $notifyData['prismNotifyName'] = 'afterSalesUpdateStatus';
        $notifyData['aftersales_bn'] = $data['aftersales_bn'];
        $notifyData['tid'] = $data['tid'];
        $notifyData['oid'] = $data['oid'];
        $notifyData['user_id'] = $data['user_id'];
        $notifyData['shop_id'] = $shopId;

        return kernel::single('system_prism_notify')->write($shopId, $notifyData);
    }

    /**
     * 商家审查售后，同意或者拒绝消息
     */
    public function afterSalesCheck($data, $shopId)
    {
        //判断消息是否需要推送到prism
        if( !$this->__check($shopId) ) return true;

        $notifyData['prismNotifyName'] = 'afterSalesCheck';
        $notifyData['aftersales_bn'] = $data['aftersales_bn'];
        $notifyData['status'] = $data['status'];
        $notifyData['tid'] = $data['tid'];
        $notifyData['oid'] = $data['oid'];
        $notifyData['shop_id'] = $shopId;

        return kernel::single('system_prism_notify')->write($shopId, $notifyData);
    }

    /**
     * 买家退货给卖家消息
     *
     * @param $data 买家退货消息
     * @param $shopId 接收退货的卖家店铺ID
     */
    public function buyerReturnGoods($data, $shopId)
    {
        //判断消息是否需要推送到prism
        if( !$this->__check($shopId) ) return true;

        $notifyData['prismNotifyName'] = 'buyerReturnGoods';
        $notifyData['aftersales_bn'] = $data['aftersales_bn'];

        $notifyData['corp_code'] = $data['corp_code'];
        $notifyData['logi_name'] = $data['logi_name'];
        $notifyData['logi_no'] = $data['logi_no'];
        $notifyData['receiver_address'] = $data['receiver_address'];
        $notifyData['mobile'] = $data['mobile'];
        $notifyData['tid'] = $data['tid'];
        $notifyData['oid'] = $data['oid'];
        $notifyData['shop_id'] = $shopId;

        return kernel::single('system_prism_notify')->write($shopId, $notifyData);
    }

    /**
     * 买家退货给卖家消息
     *
     * @param $data 买家退货消息
     * @param $shopId 接收退货的卖家店铺ID
     */
    public function sellerSendGoods($data, $shopId)
    {
        //判断消息是否需要推送到prism
        if( !$this->__check($shopId) ) return true;

        $notifyData['prismNotifyName'] = 'sellerSendGoods';
        $notifyData['aftersales_bn'] = $data['aftersales_bn'];
        $notifyData['tid'] = $data['tid'];
        $notifyData['oid'] = $data['oid'];
        $notifyData['shop_id'] = $shopId;
        //重新发货的物流信息
        $notifyData['corp_code'] = $data['corp_code'];
        $notifyData['logi_name'] = $data['logi_name'];
        $notifyData['logi_no'] = $data['logi_no'];

        return kernel::single('system_prism_notify')->write($shopId, $notifyData);
    }
}


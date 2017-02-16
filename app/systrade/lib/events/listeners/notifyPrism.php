<?php
/**
 * 订单事件触发将消息通知到prism
 *
 */
class systrade_events_listeners_notifyPrism implements base_events_interface_queue {

    private function __check($shopId)
    {

        $requestParams = ['shop_id'=>$shopId];
        $shopConf = app::get('systrade')->rpcCall('open.shop.develop.conf', $requestParams);
        if( $shopConf['develop_mode'] != 'DEVELOP' ) return false;

        return true;
    }

    /**
     * 创建订单成功后，将消息通知到prism
     *
     * @param array $data 保存的订单结构
     * @param array $special 指定的参数
     */
    public function tradeCreate($data, $special)
    {
        $objPrismNotify = kernel::single('system_prism_notify');
        foreach( $data['trade'] as $shopData )
        {
            if( !$this->__check($shopData['shop_id']) )
            {
                continue;
            }
            $notifyData['prismNotifyName'] = 'tradeCreate';
            $notifyData['tid'] = $shopData['tid'];
            $notifyData['shop_id'] = $shopData['shop_id'];
            $notifyData['user_id'] = $data['user_id'];
            $notifyData['payment'] = $shopData['payment'];
            $oids = array_column($shopData['order'], 'oid');
            $notifyData['oids'] = implode(',',$oids);
            $result = $objPrismNotify->write($notifyData['shop_id'], $notifyData);

            if( $result == 'error' ) return false;
        }

        return true;
    }

    /**
     * 修改运费，编辑订单金额消息通知到prism
     */
    public function tradeEditPrice($tid, $shopId, $data)
    {
        if( !$this->__check($shopId) ) return true;

        $objPrismNotify = kernel::single('system_prism_notify');
        $notifyData['prismNotifyName'] = 'tradeEditPrice';
        $notifyData['tid'] = $tid;
        $notifyData['shop_id'] = $shopId;
        $notifyData['payment'] = $data['payment'];
        $notifyData['post_fee'] = $data['post_fee'];
        return $objPrismNotify->write($notifyData['shop_id'], $notifyData);
    }

    /**
     * 订单付款完成 将消息通知到prism
     *
     * @param $tid string 订单ID
     * @param $payment string 订单付款金额
     * @param $shopId int 店铺ID
     */
    public function tradePay($tid, $payment, $shopId)
    {
        if( !$this->__check($shopId) ) return true;

        $objPrismNotify = kernel::single('system_prism_notify');
        $notifyData['prismNotifyName'] = 'tradePay';
        $notifyData['tid'] = $tid;
        $notifyData['shop_id'] = $shopId;
        $notifyData['payment'] = $payment;
        return $objPrismNotify->write($notifyData['shop_id'], $notifyData);
    }

    /**
     * 发货完成,将消息通知到prism
     *
     * @param array $tradeData 发货的订单信息
     * @param array $shipData  发货信息
     */
    public function tradeDelivery($tradeData, $shipData)
    {
        if( !$this->__check($tradeData['shop_id']) )
        {
            return true;
        }

        $objPrismNotify = kernel::single('system_prism_notify');

        $notifyData['prismNotifyName'] = 'tradeDelivery';
        $notifyData['tid'] = $tradeData['tid'];
        $notifyData['oids'] = $tradeData['oids'];
        $notifyData['shop_id'] = $tradeData['shop_id'];
        $notifyData['post_fee'] = $tradeData['post_fee'];

        $notifyData['corp_code'] = $shipData['corp_code'];
        $notifyData['logi_no'] = $shipData['logi_no'];
        $notifyData['ziti_memo'] = $shipData['ziti_memo'];
        $notifyData['memo'] = $shipData['memo'];

        return $objPrismNotify->write($notifyData['shop_id'], $notifyData);
    }

    /**
     * 确认收货,将消息通知到prism
     *
     * @param array $data 订单数据
     * @param array $operator 操作者数据
     */
    public function tradeConfirm($tradeData, $operator)
    {
        if( !$this->__check($tradeData['shop_id']) )
        {
            return true;
        }

        $objPrismNotify = kernel::single('system_prism_notify');

        $notifyData['prismNotifyName'] = 'tradeConfirm';
        $notifyData['tid'] = $tradeData['tid'];
        $notifyData['user_id'] = $tradeData['user_id'];
        $notifyData['shop_id'] = $tradeData['shop_id'];
        $oids = array_column($tradeData['order'], 'oid');
        $notifyData['oids'] = implode(',',$oids);
        return $objPrismNotify->write($notifyData['shop_id'], $notifyData);
    }

    /**
     * 取消订单成功, 将消息通知到prism
     *
     * @param string $tid
     * @param string $cancelReason
     */
    public function tradeClose($tid, $shopId, $cancelReason)
    {
        if( !$this->__check($shopId) )
        {
            return true;
        }

        $objPrismNotify = kernel::single('system_prism_notify');

        $notifyData['prismNotifyName'] = 'tradeClose';
        $notifyData['tid'] = $tid;
        $notifyData['shop_id'] = $shopId;
        $notifyData['cancel_reason'] = $cancelReason;

        return $objPrismNotify->write($notifyData['shop_id'], $notifyData);
    }

    /**
     * 订单退款消息, 将消息通知到prism
     *
     * @param string $tid
     */
    public function tradeRefund($tid, $shopId)
    {
        if( !$this->__check($shopId) )
        {
            return true;
        }

        $objPrismNotify = kernel::single('system_prism_notify');

        $notifyData['prismNotifyName'] = 'tradeRefund';
        $notifyData['tid'] = $tid;
        $notifyData['shop_id'] = $shopId;

        return $objPrismNotify->write($notifyData['shop_id'], $notifyData);
    }
}


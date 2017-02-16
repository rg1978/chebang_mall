<?php

class systrade_data_trade_confirm {

    public function __construct()
    {
        $this->objTrade = kernel::single('systrade_data_trade');
    }

    /**
     * 最终的克隆方法，禁止克隆本类实例，克隆是抛出异常。
     * @params null
     * @return null
     */
    final public function __clone()
    {
        throw new \Exception(app::get('systrade')->_("此类对象不能被克隆！"));
    }

    private function __check($tradeInfo)
    {
        if( !$tradeInfo )
        {
            throw new \LogicException("没有需要完成的订单!");
        }

        if($tradeInfo['status'] != "WAIT_BUYER_CONFIRM_GOODS")
        {
            throw new \LogicException("未发货订单不可确认收货");
        }

        if($tradeInfo['cancel_status'] && !in_array($tradeInfo['cancel_status'], ['NO_APPLY_CANCEL','FAILS']) )
        {
            throw new \LogicException("该订单已经处于退款阶段，不能确认收货");
        }

        return true;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * 订单确认完成
     * @params array - 订单数据
     * @params object - 控制器
     * @params string - 支付单生成的记录
     * @return boolean - 成功与否
     */
    public function generate($tid, $userId=null, $shopId=null)
    {
        $filter['tid'] = $tid;
        if( $userId ) $filter['user_id'] = intval($userId);
        if( $shopId ) $filter['shop_id'] = intval($shopId);

        $tradeInfo = $this->objTrade->getTradeInfo('status,user_id,shop_id,obtain_point_fee,consume_point_fee,payment,post_fee,points_fee,cancel_status',$filter);

        $this->__check($tradeInfo);

        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try
        {
            //生成结算点明细
            $isClearing = app::get('systrade')->rpcCall('clearing.detail.add',['tid'=>$tid]);
            if( ! $isClearing )
            {
                throw new \LogicException("结算明细生成失败");
            }

            $update['filter'] = $filter;
            $update['data'] = [
                'status' => 'TRADE_FINISHED',
                'is_clearing' => 1,
                'modified_time' => time(),
                'end_time' => time(),
            ];

            if( ! $this->objTrade->updateTrade($update) )
            {
                throw new \LogicException("订单完成失败，更新数据库失败");
            }

            $objMdlOrder = app::get('systrade')->model('order');
            if( !$objMdlOrder->update(['status'=>'TRADE_FINISHED','end_time'=>time()], ['tid'=>$tid]) )
            {
                throw new \LogicException("订单的子订单完成失败，更新数据库失败");
            }

            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollback();
            throw $e;
        }

       $this->confirmTradeEvent($tradeInfo);

        return true;
    }

    /**
     * 确认收货触发的事件
     *
     * @param array $tradeInfo 订单数据
     */
    public function confirmTradeEvent($tradeInfo)
    {
        $data['tid'] = $tradeInfo['tid'];
        $data['user_id'] = $tradeInfo['user_id'];
        $data['shop_id'] = $tradeInfo['shop_id'];
        //积分
        $data['obtain_point_fee'] = $tradeInfo['obtain_point_fee'];
        $data['consume_point_fee'] = $tradeInfo['consume_point_fee'];
        $data['payment'] = $tradeInfo['payment'];
        $data['post_fee'] = $tradeInfo['post_fee'];
        $data['points_fee'] = $tradeInfo['points_fee'];

        foreach( $tradeInfo['order']  as $key=>$val)
        {
            $orderData[$key]['oid'] = $val['oid'];
            $orderData[$key]['item_id'] = $val['item_id'];
            $orderData[$key]['num'] = $val['num'];
        }

        $data['order'] = $orderData;

        event::fire('trade.confirm', [$data, $this->operator]);
    }
}


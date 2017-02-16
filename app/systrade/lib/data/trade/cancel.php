<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class systrade_data_trade_cancel
{
    /**
     * 取消订单操作用户类型
     * shop 商家取消订单
     * buyer 用户取消订单
     * shopadmin 平台操作取消订单
     * system  系统取消订单 系统只能取消未付款的订单
     */
    protected $cancelFromType = null;

    /**
     * 当前取消订单的状态
     */
    private $__tradeStatus = null;

    /**
     * 当前执行取消操作的操作员ID
     */
    protected $id = null;

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
        $msg = app::get('systrade')->_("此类对象不能被克隆！");
        throw new \Exception($msg);
    }

    /**
     * 设置取消订单用户类型
     */
    public function setCancelFromType($type)
    {
        $this->cancelFromType = $type;
        return $this;
    }

    /**
     * 获取当前取消订单的操作用户类型
     */
    public function getCancelFromType()
    {
        return $this->cancelFromType;
    }

    /**
     * 设置当前取消订单的操作员ID
     *
     * 如果操作类型为用户则为用户ID
     * 如果操作类型为商家则为店铺ID
     * 如果操作类型为平台则为平台账号ID
     */
    public function setCancelId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 获取当前取消订单的操作员ID
     */
    public function getCancelId()
    {
        return $this->id;
    }

    /**
     * 获取当前取消订单的订单数据
     */
    private function __getCancelTradeInfo($tid)
    {
        if( $this->getCancelFromType() == 'buyer' )
        {
            $searchTradeParam['filter']['user_id'] = $this->getCancelId();
        }
        elseif( $this->getCancelFromType() == 'shop' )
        {
            $searchTradeParam['filter']['shop_id'] = $this->getCancelId();
        }

        $searchTradeParam['filter']['tid'] = $tid;
        $searchTradeParam['rows'] = "tid,status,pay_type,user_id,shop_id,payed_fee,post_fee";
        $tradeData = $this->objTrade->getTradeList($searchTradeParam,false);
        if( !$tradeData )
        {
            $msg = "取消的订单不存在";
            throw new \logicexception($msg);
        }

        $this->__tradeStatus = $tradeData[0]['status'];

        return $tradeDataInfo = $tradeData[0];
    }

    /**
     *  创建取消订单记录列表
     *
     * @param $tid array | int  需要取消的订单ID
     * @param $cancelReason string 取消订单的原因
     *
     * @return bool
     */
    public function create($tid, $cancelReason, $refundBn,$returnFreight=null)
    {
        $cancelReason = trim($cancelReason);
        $tradeDataInfo = $this->__getCancelTradeInfo($tid);

        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try {
            //未付款 可直接取消 货到付款并且不是消费者申请取消订单则可以直接取消
            if( $tradeDataInfo['status'] == 'WAIT_BUYER_PAY' || ($tradeDataInfo['pay_type'] == 'offline' && $this->getCancelFromType() != 'buyer' ) )
            {
                $this->__noPayTradeCancel($tradeDataInfo, $cancelReason);
            }
            else//已付款或者为货到付款的订单需要申请退款
            {
                $this->__payTradeCancel($tradeDataInfo, $cancelReason, $refundBn,$returnFreight);
            }

            $db->commit();
        }
        catch( Exception $e) {
            $db->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 未支付的订单取消 可直接取消的订单，未支付并且为在线支付
     */
    private function __noPayTradeCancel($tradeData, $cancelReason)
    {
        $tid = $tradeData['tid'];
        $cancelTradeData['tid'] = $tid;
        $cancelTradeData['user_id'] = $tradeData['user_id'];
        $cancelTradeData['shop_id'] = $tradeData['shop_id'];
        $cancelTradeData['reason'] = $cancelReason;//取消订单原因
        $cancelTradeData['pay_type'] = $tradeData['pay_type'];//取消的订单的支付类型
        $cancelTradeData['payed_fee'] = ( $tradeData['payed_fee'] && $tradeData['payed_fee'] > 0) ? $tradeData['payed_fee'] : '0' ;//取消的订单的已支付金额
        $cancelTradeData['cancel_from'] = $this->getCancelFromType();
        //线上支付，未支付直接完成
        $cancelTradeData['process'] = '3';//取消订单处理完成
        $cancelTradeData['refunds_status'] = 'SUCCESS';//退款状态为成功

        $cancelTradeData['created_time'] = time();
        $cancelTradeData['modified_time'] = time();

        if( !$cancelId = app::get('systrade')->model('trade_cancel')->insert($cancelTradeData) )
        {
            throw new \Exception("取消订单失败");
        }

        //记录取消订单处理日志
        $logText = '您的申请已提交';
        $this->__addLog($cancelId, $logText);

        //订单取消成功后进行的操作
        $this->__cancelSuccDo($tid, $tradeData['shop_id'], $cancelReason, $status);

        //记录取消订单处理日志
        $logText = '您的订单取消成功';
        $this->__addLog($cancelId, $logText, 'system');

        return true;
    }

    /**
     * 取消订单成功后处理事件
     */
    private function __cancelSuccDo($tid, $shopId, $cancelReason)
    {
        //取消订单成功更新订单状态和信息
        $params['filter']['tid'] = $tid;
        $params['data']['end_time'] = time();
        if( $this->getCancelFromType() == 'buyer' && $this->__tradeStatus != 'WAIT_BUYER_PAY' )
        {
            $params['data']['status'] = 'TRADE_CLOSED';
            $orderParams['status'] = 'TRADE_CLOSED_AFTER_PAY';
        }
        else
        {
            $params['data']['status'] = 'TRADE_CLOSED_BY_SYSTEM';
            $orderParams['status'] = 'TRADE_CLOSED_BEFORE_PAY';
        }

        if(!$this->objTrade->updateTrade($params))
        {
            throw new \Exception("取消订单失败，更新数据库失败");
        }

        //取消订单成功更新字订单信息
        $objMdlOrder = app::get('systrade')->model('order');
        $orderParams['end_time'] = time();
        if(!$objMdlOrder->update($orderParams,$params['filter']))
        {
            throw new \Exception("取消订单失败，更新数据库失败");
        }

        //取消订单后，积分回退
        if(!$this->__rollbackPoint($tid))
        {
            throw new \Exception("取消订单{$tid}失败，积分回退失败");
        }

        // 恢复、解冻库存
        if( !$this->__recoverStore($tid) )
        {
            throw new \Exception("取消订单{$tid}失败，恢复库存失败");
        }

        $this->updateCancelStatus($tid, 'SUCCESS', $cancelReason);

        // 返还优惠券，如果有的情况下
        if( !app::get('systrade')->rpcCall('user.coupon.back', array('tid'=>$tid)) )
        {
            throw new \Exception("取消订单{$tid}失败，退还优惠券失败");
        }

        if(  !$this->__rollbackHongbao($tid) )
        {
            throw new \Exception("取消订单{$tid}失败，红包退还失败，请重新设置");
        }

        event::fire('trade.close',[$tid, $shopId, $cancelReason]);

        return true;
    }

    /**
     * 已支付订单取消
     *
     * @param $tradeData array 要取消的订单数据
     * @param $cancelReason string 取消订单的原因
     *
     * @return bool
     */
    private function __payTradeCancel($tradeData, $cancelReason, $refundBn,$returnFreight=null)
    {
        //创建退款申请单
        if( $this->getCancelFromType() == 'buyer' )
        {
            $params['status'] = '0';//申请状态 未审核
            $cancelTradeData['refunds_status'] = 'WAIT_CHECK';//退款状态 等待审核
            $process = '0';
            $tradeCancelSatus = 'WAIT_PROCESS';
        }
        elseif( $this->getCancelFromType() == 'shop' )
        {
            $params['status'] = '5';//申请状态 商家强制关单
            $cancelTradeData['refunds_status'] = 'WAIT_REFUND';//退款状态 直接等待平台退款
            $process = '2';
            $tradeCancelSatus = 'REFUND_PROCESS';
        }
        else
        {
            $params['status'] = '6';//申请状态 平台强制关单
            $cancelTradeData['refunds_status'] = 'WAIT_REFUND';//退款状态 直接等待平台退款
            $process = '2';
            $tradeCancelSatus = 'REFUND_PROCESS';
        }

        $tid = $tradeData['tid'];
        //发货完成后取消订单（拒收）时，先生成结算明细
        if($tradeData['status'] == "WAIT_BUYER_CONFIRM_GOODS")
        {
            $isClearing = app::get('systrade')->rpcCall('clearing.detail.add',['tid'=>$tid]);
            if( ! $isClearing )
            {
                throw new \LogicException("结算明细生成失败");
            }
        }

        $cancelTradeData['tid'] = $tid;
        $cancelTradeData['user_id'] = $tradeData['user_id'];
        $cancelTradeData['shop_id'] = $tradeData['shop_id'];
        $cancelTradeData['cancel_from'] = $this->getCancelFromType();
        $cancelTradeData['reason'] = $cancelReason;//取消订单原因
        $cancelTradeData['pay_type'] = $tradeData['pay_type'];//取消的订单的支付类型
        $cancelTradeData['payed_fee'] = $tradeData['payed_fee'];//取消的订单的已支付金额
        $cancelTradeData['process'] = $process;//处理进度,提交申请
        $cancelTradeData['created_time'] = time();
        $cancelTradeData['modified_time'] = time();

        if( !$cancelId = app::get('systrade')->model('trade_cancel')->insert($cancelTradeData) )
        {
            throw new \Exception("取消订单失败");
        }

        $logText = '您的申请已提交';
        $this->__addLog($cancelId, $logText);

        $params['shop_id'] = $tradeData['shop_id'];
        $params['reason'] = $cancelReason;
        $params['tid'] = $tid;
        $params['refunds_type'] = 'cancel';//申请退款类型，取消订单退款
        if(!is_null($returnFreight))
        {
            $params['return_freight'] = $returnFreight;
        }

        if( $refundBn )
        {
            $params['refund_bn'] = $refundBn;
        }

        $refundapplyData = app::get('systrade')->rpcCall('aftersales.refundapply.create', $params);
        if( !$refundapplyData )
        {
            throw new \Exception("取消订单失败");
        }

        //is_restore 表示已经退款完成
        //在红包全额支付的情况下，不需要平台退款，创建好退款申请单后直接进行了退款
        if( ! $refundapplyData['is_restore'] )
        {
            $this->updateCancelStatus($tid, $tradeCancelSatus, $cancelReason);
        }

        return true;
    }

    /**
     * 更新取消订单状态
     *
     * @param $tid
     * @param $cancelStatus
     */
    public function updateCancelStatus($tid, $cancelStatus, $cancelReason)
    {
        $objMdlTrade = app::get('systrade')->model('trade');
        $updateData['cancel_status'] = $cancelStatus;
        if( $cancelReason )
        {
            $updateData['cancel_reason'] = $cancelReason;
        }

        if( !$objMdlTrade->update($updateData, ['tid'=>$tid]) )
        {
            throw new \Exception("更新取消订单状态失败");
        }
        return true;
    }

    /**
     * 恢复取消订单的库存
     *
     * @param $tid string 单个订单号
     *
     * @return bool
     */
    private function __recoverStore($tid)
    {
        $isRecover = true;
        $orderInfo = app::get('systrade')->model('order')->getList('oid,shop_id,status,item_id,sku_id,num,sub_stock,pay_time,gift_data', array('tid'=>$tid));
        foreach ($orderInfo as $oVal)
        {
            $tradePay = 1;
            if(!$oVal['pay_time'])
            {
                if($oVal['status'] == 'WAIT_BUYER_PAY' || $oVal['status'] == 'TRADE_CLOSED_BEFORE_PAY')
                {
                    $tradePay = 0;
                }
            }

            $arrParam = array(
                'item_id'  => $oVal['item_id'],
                'sku_id'   => $oVal['sku_id'],
                'quantity' => $oVal['num'],
                'sub_stock' => $oVal['sub_stock'],
                'tradePay' => $tradePay,
            );
            $isRecover = app::get('systrade')->rpcCall('item.store.recover',$arrParam);
            if(!$isRecover) return false;


            if($oVal['gift_data'])
            {
                foreach($oVal['gift_data'] as $giftVal)
                {
                    $arrParam = array(
                        'item_id'  => $giftVal['item_id'],
                        'sku_id'   => $giftVal['sku_id'],
                        'quantity' => $giftVal['gift_num'],
                        'sub_stock' => $giftVal['sub_stock'],
                        'tradePay' => $tradePay,
                    );
                    $isRecover = app::get('systrade')->rpcCall('item.store.recover',$arrParam);
                    if(!$isRecover) return false;
                }

            }
        }

        return $isRecover;
    }

    /**
     * 消费者申请取消订单，商家审核同意取消订单
     */
    public function cancelShopAgree($cancelId, $shopId)
    {
        $tradeCancelData = $this->__preCancelData($cancelId, $shopId);

        $db = app::get('systrade')->database();
        $db->beginTransaction();
        $tid = $tradeCancelData['tid'];
        try
        {
            if( $tradeCancelData['pay_type'] == 'online' )
            {
                //更新取消订单记录状态 退款状态为等待退款
                app::get('systrade')->model('trade_cancel')->update(['refunds_status'=>'WAIT_REFUND','process'=>'2'],['cancel_id'=>$cancelId]);
                //更新订单表取消订单的状态
                $this->updateCancelStatus($tid, 'REFUND_PROCESS');

                //更新退款申请单状态
                $params['shop_id'] = $shopId;
                $params['status'] = '3';
                $params['tid'] = $tid;
                $refunds = app::get('systrade')->rpcCall('aftersales.refundapply.shop.reply', $params);

                $logText = '商家同意退款，等待退款处理！';
                $this->__addLog($cancelId, $logText, $shopId, 'shop');

                $tradeInfo = app::get('systrade')->model('trade')->getRow('tid,user_id,hongbao_fee,payed_fee', array('tid'=>$tid));

                //如果是红包全额支付，自动退红包
                if( $tradeInfo['hongbao_fee'] ==  $tradeInfo['payed_fee'] )
                {
                    app::get('systrade')->rpcCall('aftersales.refunds.restore', array('refunds_id'=>$refunds['refunds_id'],'return_fee'=>$refunds['total_price']) );
                }
            }
            else//线下付款则处理完成
            {
                //更新退款申请单状态
                $params['shop_id'] = $shopId;
                $params['status'] = '1';
                $params['tid'] = $tid;
                app::get('systrade')->rpcCall('aftersales.refundapply.shop.reply', $params);

                //更新取消订单记录状态
                app::get('systrade')->model('trade_cancel')->update(['refunds_status'=>'SUCCESS','process'=>'3'],['cancel_id'=>$cancelId]);
                //订单取消成功后进行的操作
                $this->__cancelSuccDo($tid, $shopId);

                $logText = '商家同意取消订单，订单取消成功！';
                $this->__addLog($cancelId, $logText, $shopId, 'shop');

            }
            $db->commit();
        }
        catch( Exception $e )
        {
            $db->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 消费者申请取消订单，商家拒绝取消订单
     */
    public function cancelShopReject($cancelId, $shopId, $reason)
    {
        $tradeCancelData = $this->__preCancelData($cancelId, $shopId);

        $db = app::get('systrade')->database();
        $db->beginTransaction();

        $tid = $tradeCancelData['tid'];
        try{

            //更新取消订单记录状态
            app::get('systrade')->model('trade_cancel')->update(['refunds_status'=>'SHOP_CHECK_FAILS','process'=>'3','shop_reject_reason'=>$reason],['cancel_id'=>$cancelId]);
            //更新订单表取消订单的状态
            $this->updateCancelStatus($tid, 'FAILS');

            //需要商家审核的订单，为已支付订单取消，或者为货到付款
            //如果不是货到付款那么则需要更新退款申请单的状态
            if( $tradeCancelData['pay_type'] == 'online' )
            {
                $params['shop_id'] = $shopId;
                $params['status'] = '4';
                $params['tid'] = $tid;
                $refunds = app::get('systrade')->rpcCall('aftersales.refundapply.shop.reply', $params);
            }

            $logText = '商家拒绝取消订单！';
            $this->__addLog($cancelId, $logText, $shopId, 'shop');
        }
        catch( Exception $e )
        {
            $db->rollback();
            throw $e;
        }

        $db->commit();
        return true;
    }

    private function __preCancelData($cancelId, $shopId)
    {
        $tradeCancelData = app::get('systrade')->model('trade_cancel')->getRow('tid,shop_id,pay_type,refunds_status,cancel_from',['cancel_id'=>$cancelId]);
        if( !$tradeCancelData || $tradeCancelData['shop_id'] != $shopId )
        {
            throw new \LogicException('待审核的取消订单不存在');
        }

        if( $tradeCancelData['refunds_status'] != 'WAIT_CHECK' )
        {
            throw new \LogicException('该取消订单已审核，不需要审核');
        }

        return $tradeCancelData;
    }

    //取消订单，退款成功后的操作
    public function cancelSuccDo($tid, $shopId, $refundHongbaoFee)
    {
        $data = app::get('systrade')->model('trade_cancel')->getRow('shop_id,cancel_from,cancel_id,payed_fee', ['tid'=>$tid]);

        if(empty($data))
        {
            throw new \Exception("此取消订单不存在");
        }
        if($data['shop_id'] !=  $shopId )
        {
            throw new \Exception("参数错误");//当前用户和取消记录中存储的用户ID不一致
        }

        //更新取消订单记录状态 退款状态未等待退款
        app::get('systrade')->model('trade_cancel')->update(['refunds_status'=>'SUCCESS','process'=>'3'],['cancel_id'=>$data['cancel_id']]);

        $this->refundHongbaoFee = $refundHongbaoFee;

        $this->__cancelSuccDo($tid, $shopId);

        $logText = '取消订单成功，退款已处理！';
        $this->__addLog($data['cancel_id'], $logText, $shopId, 'shopadmin');

        return true;
    }

    /**
     * 记录订单取消日志
     * @param int cancelId 取消订单列表ID
     * @param array $params   成功标识
     */
    private function __addLog($cancelId, $logText, $role=null)
    {
        $objLibLog = kernel::single('systrade_data_trade_log');

        if( !$role )  $role = $this->getCancelFromType();

        if( $role == 'shop' ) $role = 'seller';

        $tradeLogData = array(
            'rel_id'   => $cancelId,
            'op_id'    => $this->getCancelId(),
            'op_role'  => $role,
            'behavior' => 'cancel',
            'log_text' => $logText,
        );

        if( !$objLibLog->addLog($tradeLogData) )
        {
            throw new \logicexception('取消订单日志保存失败');
        }

        return true;
    }

    /**
     * @brief 未发货的订单取消订单时，回退扣减的积分
     *
     * @param $tid
     *
     * @return
     */
    private function __rollbackPoint($tid)
    {
        $result = true;
        $tradeInfo = app::get('systrade')->model('trade')->getRow('tid,user_id,status,consume_point_fee', array('tid'=>$tid));
        $params['user_id'] = $tradeInfo['user_id'];
        $params['type'] = "obtain";
        $params['behavior'] = "来自于订单：".$tradeInfo['tid']."的积分回退";
        $params['remark'] = "取消订单回退积分";
        $params['num'] = $tradeInfo['consume_point_fee'];
        if($params['num'] > 0)
        {
            $result = app::get('systrade')->rpcCall('user.updateUserPoint',$params);
        }
        return $result;
    }

    public function __rollbackHongbao($tid)
    {
        $tradeInfo = app::get('systrade')->model('trade')->getRow('tid,user_id,hongbao_fee,user_hongbao_id', array('tid'=>$tid));
        if( $tradeInfo['hongbao_fee'] <= 0 )
        {
            return true;
        }

        $params['user_id'] = $tradeInfo['user_id'];
        $params['money'] = $this->refundHongbaoFee ?: $tradeInfo['hongbao_fee'];
        $params['hongbao_obtain_type'] = 'cancelTrade';
        $params['user_hongbao_id'] = $tradeInfo['user_hongbao_id'];
        $params['tid'] = $tradeInfo['tid'];

        return app::get('systrade')->rpcCall('user.hongbao.refund',$params);
    }
}


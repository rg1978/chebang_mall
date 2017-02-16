<?php
/** * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

//订单发货
class systrade_data_trade_delivery
{

    /**
     * 当前发货生成的发货单号
     */
    protected $deliveryId = null;

    /**
     * 检查订单是否可以发货
     *
     * @param $tradeInfo array 订单数据
     */
    private function __check($tradeInfo, $shopUserData)
    {
        if( empty($tradeInfo) || $tradeInfo['shop_id'] != $shopUserData['shop_id'] )
        {
            $message = app::get('systrade')->_('发货订单不存在');
            throw new LogicException($message);
        }

        if( $tradeInfo['status'] != 'WAIT_SELLER_SEND_GOODS' )
        {
            $message = app::get('systrade')->_('订单已发货');
            throw new LogicException($message);
        }

        #if( in_array($tradeInfo['cancel_status'],['WAIT_PROCESS','REFUND_PROCESS']) )
        #{
        #    $message = app::get('systrade')->_('订单正在取消处理');
        #    throw new LogicException($message);
        #}

        return true;
    }

    /**
     * 获取需要发货的订单数据
     *
     * @param $tid string 订单ID
     */
    private function __getTradeInfo($tid)
    {
        $objLibTrade = kernel::single('systrade_data_trade');

        //订单返回的字段，如果有值则返回所有子订单的所有数据
        $returnCols = 'post_fee,dlytmpl_ids,cancel_status,status,shop_id';
        $tradeInfo = $objLibTrade->getTradeInfo($returnCols, ['tid'=>$tid]);

        return $tradeInfo;
    }

    /**
     * 对订单进行发货
     *
     * @param $tid string 发货的订单
     * @param $corpCode 物流公司编号
     * @param $logiNo 运单号
     * @param $shopUserData 发货商家用户信息 shop_id seller_id
     * @param $zitiMemo 自提备注
     * @param $memo 发货备注
     */
    public function doDelivery($tid, $corpCode, $logiNo, $shopUserData, $zitiMemo, $memo)
    {
        $shopId = $shopUserData['shop_id'];
        $sellerId = $shopUserData['seller_id'];

        $tradeInfo = $this->__getTradeInfo($tid);

        //检查订单是否可以发货
        $this->__check($tradeInfo, $shopUserData);

        $db = app::get('systrade')->database();
        $db->beginTransaction();

        $oids = implode(',',array_column($tradeInfo['order'],'oid'));
        try
        {
            //如果订单正在进行取消，发货的时候拒绝消费者取消订单
            //业务场景为：
            //消费者提交了取消订单申请，但是在后端（OMS）仓库发货未发现该订单取消申请（网络异常，退款申请单同步失败），进行了发货！
            //那么则将消费者的申请拒绝，可以让消费者进行拒收操作
            if( in_array($tradeInfo['cancel_status'],['WAIT_PROCESS','REFUND_PROCESS']) )
            {

                $tradecanceData = app::get('systrade')->model('trade_cancel')->getRow('cancel_id', ['tid'=>$tid]);

                $reason = app::get('systrade')->_('商家已发货，拒绝取消订单');
                //商家审核拒绝取消订单
                kernel::single('systrade_data_trade_cancel')->cancelShopReject($tradecanceData['cancel_id'], $tradeInfo['shop_id'], $reason);
            }

            //创建发货单
            $this->_createDelivery($tid, $oids, $shopId, $sellerId);

            $dlytmplId = $tradeInfo['dlytmpl_ids'];
            $postFee = $tradeInfo['post_fee'];
            //更新发货单
            $detail = $this->_updateDelivery($tid, $dlytmplId, $postFee, $corpCode, $logiNo, $memo);

            //更新订单发货状态
            $tradeData = array(
                'status' => 'WAIT_BUYER_CONFIRM_GOODS',
                'consign_time' => time(),
                'ziti_memo' => $zitiMemo,
            );
            $objMdlTrade = app::get('systrade')->model('trade');
            if( ! $objMdlTrade->update($tradeData, ['tid'=>$tid]) )
            {
                throw new LogicException("更新订单发货状态失败");
            }

            //更新子订单状态
            $objMdlOrder = app::get('systrade')->model('order');
            foreach($tradeInfo['order'] as $value)
            {
                //删除赠品
                $updateData['sendnum'] = ecmath::number_plus(array($value['sendnum'], $detail[$value['oid']]['number']));
                $updateData['status'] = "WAIT_BUYER_CONFIRM_GOODS";
                $updateData['consign_time'] = time();
                if( ! $objMdlOrder->update($updateData, [ 'oid'=>$value['oid'] ]) )
                {
                    throw new LogicException("更新子订单发货状态失败");
                }
            }

            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        $shipData['corp_code'] = $corpCode;
        $shipData['logi_no'] = $logiNo;
        $shipData['ziti_memo'] = $zitiMemo;
        $shipData['memo'] = $memo;

        $tradeData['tid'] = $tid;
        $tradeData['oids'] = $oids;//逗号隔开的字符串
        $tradeData['shop_id'] = $shopId;
        $tradeData['post_fee'] = $postFee;
        event::fire('trade.delivery',[$tradeData, $shipData]);

        return true;
    }

    /**
     * 创建发货单
     *
     * @param $tid string 订单ID
     * @param $oids string 子订单ID集合
     * @param $shopId 店铺ID
     * @param $sellerId 商家用户ID
     *
     * @return $deliveryId 返回发货单
     */
    protected function _createDelivery($tid, $oids, $shopId, $sellerId)
    {
        $data = [
            'tid' => $tid,
            'oids' => $oids,
            'shop_id' => $shopId,
            'seller_id' => $sellerId,
        ];
        $this->deliveryId = app::get('systrade')->rpcCall('delivery.create',$data);

        if( ! $this->deliveryId )
        {
            throw new LogicException("创建发货单失败");
        }

        return $this->deliveryId;
    }

    /**
     * 更新发货单
     *
     * @param $tid string 订单ID
     * @param $dlytmplId int 当前发货订单的快递单模版ID
     * @param $postFee float 当前发货的运费
     * @param $corpCode string 物流公司编号
     * @param $logiNo string 运单号
     * @param $memo 发货备注
     *
     * @return array
     */
    protected function _updateDelivery($tid, $dlytmplId, $postFee, $corpCode, $logiNo, $memo)
    {
        //更新发货单状态
        $deliveryData = array(
            'delivery_id' => $this->deliveryId,
            'template_id' => $dlytmplId,
            'logi_no' => $logiNo,
            'tid' => $tid,
            'post_fee' => $postFee ? $postFee : 0,
            'corp_code' => $corpCode,
            'memo' => $memo,
        );

        $result = app::get('systrade')->rpcCall('delivery.update',$deliveryData);
        foreach($result['detail'] as $key=>$value)
        {
            if($value['item_type'] == "gift")
            {
                unset($result['detail'][$key]);
            }
        }

        $detail = array_bind_key($result['detail'],"oid");

        return $detail;
    }
}


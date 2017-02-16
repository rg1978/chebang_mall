<?php
/**
 * 创建订单事件 trade.create
 *
 * 事件任务说明：生成订单日志
 *
 * 异步
 */
class systrade_events_listeners_createTradelog implements base_events_interface_queue {

    /**
     * 创建订单成功后，生成订单日志
     *
     * @param array $data 保存的订单结构
     * @param array $special 指定的参数
     */
    public function addTradeLog($data, $special)
    {
        $objLibLog = kernel::single('systrade_data_trade_log');

        $logText = '订单创建成功！';
        foreach( $data['trade'] as $shopTradeData )
        {
            $sdfTradeLog = array(
                'rel_id'   => $shopTradeData['tid'],
                'op_id'    => $data['user_id'],
                'op_name'  => (! $data['user_name']) ? app::get('systrade')->_('买家') : $data['user_name'],
                'op_role'  => 'buyer',
                'behavior' => 'create',
                'log_text' => $logText,
            );

            if( ! $objLibLog->addLog($sdfTradeLog) )
            {
                $message = '订单生成失败[日志]';
                logger::info('event listeners_confirmExperience:'.$message);
                throw new \LogicException($message);
            }
        }

        return true;
    }

    /**
     * 创建订单成功后，生成订单优惠日志
     *
     * @param array $data 保存的订单结构
     * @param array $special 指定的参数
     */
    public function addPromotionLog($data, $special)
    {
        $objMdlPromDetail = app::get('systrade')->model('promotion_detail');

        foreach( $data['trade'] as $shopTradeData )
        {
            foreach($shopTradeData['order'] as $orderData)
            {
                $shopId = $orderData['shop_id'];
                $usedCartPromotion = $special['cartPromotion'][$shopId]['usedCartPromotion'];
                $basicPromotionListInfo = $special['cartPromotion'][$shopId]['basicPromotionListInfo'];

                if( $usedCartPromotion && $orderData['selected_promotion'] && in_array($orderData['selected_promotion'], $usedCartPromotion) )
                {
                    $promLogData = [
                        'tid'   => $orderData['tid'],
                        'oid'   => $orderData['oid'],
                        'user_id'    => $orderData['user_id'],
                        'item_id'    => $orderData['item_id'],
                        'sku_id'    => $orderData['sku_id'],
                        'promotion_id' => $orderData['selected_promotion'],
                        'promotion_type' => $basicPromotionListInfo[$orderData['selected_promotion']]['promotion_type'],
                        'promotion_tag' => $basicPromotionListInfo[$orderData['selected_promotion']]['promotion_tag'],
                        'promotion_name' => $basicPromotionListInfo[$orderData['selected_promotion']]['promotion_name'],
                        'promotion_desc' => $basicPromotionListInfo[$orderData['selected_promotion']]['promotion_desc'],
                    ];

                    if( ! $objMdlPromDetail->save($promLogData) )
                    {
                        throw new \LogicException(app::get('systrade')->_('生成订单优惠日志失败'));
                    }
                }

                if( $orderData['activityDetail'] )
                {
                    $promLogData = array(
                        'tid'   => $orderData['tid'],
                        'oid'   => $orderData['oid'],
                        'user_id'    => $orderData['user_id'],
                        'item_id'    => $orderData['item_id'],
                        'sku_id'    => $orderData['sku_id'],
                        'promotion_id' => $orderData['activityDetail']['activity_id'],
                        'promotion_type' => 'activity',
                        'promotion_tag' => $orderData['activityDetail']['activity_info']['activity_tag'],
                        'promotion_name' => $orderData['activityDetail']['activity_info']['activity_name'],
                        'promotion_desc' => $orderData['activityDetail']['activity_info']['description'],
                    );

                    if( ! $objMdlPromDetail->save($promLogData) )
                    {
                        throw new \LogicException(app::get('systrade')->_('生成订单优惠日志失败'));
                    }
                }
            }
        }

        return true;
    }
}


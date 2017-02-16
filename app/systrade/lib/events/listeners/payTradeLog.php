<?php
/**
 * 订单支付完成事件
 *
 * 事件任务说明：记录订单支付完成LOG
 * 支持同步/异步事件任务
 */
class systrade_events_listeners_payTradeLog implements base_events_interface_queue {

    /**
     * 记录订单支付完成LOG
     *
     * @param array $tid 支付的订单
     * @param array $payment 支付的金额
     */
    public function handle($tid, $payment)
    {
        $objLibLog = kernel::single('systrade_data_trade_log');
        $logText = '订单付款成功！';

        $sdfTradeLog = array(
            'rel_id'   => $tid,
            'op_id'    => 0,
            'op_name'  => '系统',
            'op_role'  => 'system',
            'behavior' => 'payment',
            'log_text' => $logText,
        );

        if( !$objLibLog->addLog($sdfTradeLog) )
        {
            throw new \LogicException('log记录失败');
        }

        return true;
    }
}


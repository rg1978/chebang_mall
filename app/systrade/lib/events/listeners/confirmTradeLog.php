<?php
/**
 * 确认收货订单事件
 *
 * 事件任务说明：确认收货日志
 */
class systrade_events_listeners_confirmTradeLog implements base_events_interface_queue {

    /**
     * 确认收货订单事件，确认收货日志
     *
     * @param array $data 保存的订单结构
     * @param array $operator 操作员参数
     */
    public function handle($data, $operator)
    {
        $objLibLog = kernel::single('systrade_data_trade_log');

        $logText = '确认订单成功！';

        $logData = array(
            'rel_id'   => $data['tid'],
            'op_id'    => $operator['op_id'],
            'op_name'  => $operator['op_account'] ? $operator['op_account'] : '系统',
            'op_role'  => $operator['account_type'],
            'behavior' => 'confirm',
            'log_text' => $logText,
        );

        if( !$objLibLog->addLog($logData) )
        {
            $message = '订单确认收货失败[日志]';
            logger::info('event listeners_confirmTradeLog:'.$message);
            throw new \LogicException($message);
        }

        return true;
    }
}


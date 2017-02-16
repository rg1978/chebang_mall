<?php
/**
 * 确认收货订单事件
 *
 * 事件任务说明：更新商品销量
 */
class systrade_events_listeners_updateSoldQuantity implements base_events_interface_queue {

    /**
     * 确认收货订单事件，确认收货日志
     *
     * @param array $data 保存的订单结构
     * @param array $operator 操作员参数
     */
    public function handle($data, $operator)
    {
        foreach($data['order'] as $key => $val)
        {
            $apiData = array('item_id'=>$val['item_id'], 'num'=>$val['num']);
            try
            {
                if( ! app::get('systrade')->rpcCall('item.updateSoldQuantity', $apiData) )
                {
                    $message = '销量统计失败';
                    logger::info('event listeners_updateSoldQuantity:'.$message);
                    throw new \LogicException(app::get('systrade')->_($message));
                }
            }
            catch( \Exception $e )
            {
                $message = $e->getMessage();
                logger::info('event listeners_updateSoldQuantity:'.$message);
                throw new \Exception($message);
            }
        }

        return true;
    }
}


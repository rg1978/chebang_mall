<?php
/**
 * 创建订单事件
 *
 * 事件任务说明：更新活动商品的销量
 * 支持同步/异步
 */
class systrade_events_listeners_upActivitySalesCount implements base_events_interface_queue {

    /**
     * 创建订单成功后，更新活动商品的销量
     *
     * @param array $data 保存的订单结构
     * @param array $special 指定的参数
     */
    public function handle($data, $special)
    {
        foreach( $data['trade'] as $shopTradeData )
        {
            foreach($shopTradeData['order'] as $orderData)
            {
                if( ! $orderData['activityDetail'] ) continue;

                //修改活动商品表的销量字段值
                $db = app::get('syspromotion')->model('activity_item')->database();
                $sqlStr = "UPDATE syspromotion_activity_item SET sales_count=ifnull(sales_count,0)+? WHERE  item_id=? AND activity_id=?";
                if ($db->executeUpdate($sqlStr, [$orderData['num'], $orderData['item_id'], $orderData['activityDetail']['activity_id']])==0)
                {
                    throw new \LogicException(app::get('systrade')->_('活动商品销量更新失败'));
                }
            }
        }

        return true;
    }
}


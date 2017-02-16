<?php
/**
 * 确认收货订单事件
 *
 * 事件任务说明：更新积分
 */
class systrade_events_listeners_confirmPoint implements base_events_interface_queue {

    /**
     * 确认收货订单事件，更新积分
     *
     * @param array $data 保存的订单结构
     * @param array $operator 操作员参数
     */
    public function handle($data, $operator)
    {
        $params['user_id'] = $data['user_id'];
        $params['type'] = "obtain";
        $params['behavior'] = "购物获得积分";
        $params['remark'] = "当前积分来自订单：".$data['tid'];
        $params['num'] = $data['obtain_point_fee'];

        try
        {
            $result = app::get('systrade')->rpcCall('user.updateUserPoint',$params);
            if( !$result )
            {
                $message = '更新积分失败[日志]';
                logger::info('event listeners_confirmPoint:'.$message);
                throw new \LogicException($message);
            }
        }
        catch( \Exception $e )
        {
            $message = $e->getMessage();
            logger::info('event listeners_confirmPoint:'.$message);
            throw new \Exception($message);
        }

        return true;
    }
}


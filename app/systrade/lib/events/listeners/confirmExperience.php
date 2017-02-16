<?php
/**
 * 确认收货订单事件
 *
 * 事件任务说明：确认会员经验值
 */
class systrade_events_listeners_confirmExperience implements base_events_interface_queue {

    /**
     * 确认收货订单事件，确认会员经验值
     *
     * @param array $data 保存的订单结构
     * @param array $operator 操作员参数
     */
    public function handle($data, $operator)
    {
        $params['user_id'] = $data['user_id'];
        $params['type'] = "obtain";
        $params['num'] = $data['payment']+$data['points_fee'] - $data['post_fee'];
        $params['behavior'] = "购物获得经验值";
        $params['remark'] = "当前经验值来自订单：".$data['tid'];

        try
        {
            $result = app::get('systrade')->rpcCall('user.updateUserExp',$params);
            if( !$result )
            {
                $message = '更新会员经验值[日志]';
                logger::info('event listeners_confirmExperience:'.$message);
                throw new \LogicException($message);
            }
        }
        catch( \Exception $e )
        {
            $message = $e->getMessage();
            logger::info('event listeners_confirmExperience:'.$message);
            throw new \Exception($message);
        }

        return true;
    }
}


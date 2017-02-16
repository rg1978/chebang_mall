<?php
/**
 * 创建订单事件
 *
 * 事件任务说明：清除购物车数据
 * 同步事件任务
 */
class systrade_events_listeners_clearCart {

    /**
     * 创建订单成功后，清除购物车数据
     *
     * @param array $data 保存的订单结构
     * @param array $special 指定的参数
     */
    public function handle($data, $special)
    {
        if( ! $special['cartIds'] ) return true;

        $cartIds = implode(',', $special['cartIds']);

        try
        {
            $delCartResult = app::get('systrade')->rpcCall('trade.cart.delete', array('cart_id'=>$cartIds,'mode'=>$special['mode'],'user_id'=>$data['user_id']) );

            if( $delCartResult === false )
            {
                $message = '删除购物车数据错误';
                logger::info('event listeners_clearCart:'.$message);
                throw new \LogicException($message);
            }
        }
        catch( \Exception $e )
        {
            $message = $e->getMessage();
            logger::info('event listeners_clearCart:'.$message);
            throw new \Exception($message);
        }

        return true;
    }
}


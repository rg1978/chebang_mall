<?php

class ectools_tasks_deposit_payment extends base_task_abstract implements base_interface_task
{
    public function exec($params=null)
    {
        //这里做了一个验证，如果支付单状态变更成功，那么直接返回true，不再进行处理
        $paymentId = $params['payment_id'];
        $payment = app::get('ectools')->model('payments')->getRow('status', ['payment_id' => $paymentId]);
        if($payment['status'] == 'succ')
            return true;

        $url = kernel::openapi_url('openapi.ectools_payment/parse/ectools_payment_plugin_deposit', 'callback');

        //这里请求多次。如果某次请求失败了，将会重新发起请求。
        //避免某次因为网络抖动等因素导致的不稳定的扣掉预存款但是订单状态没有变更。
        $limit = 3;
        for($i=0; $i<$limit; $i++)
        {
            try
            {
                $res = client::post($url, ['body' => $params, 'timeout' => 6]);
            }
            catch(Exception $e)
            {
                if($i >= $limit - 1)
                {
                    throw $e;
                }
                continue;
            }
            break;
        }

        return true;
    }
}


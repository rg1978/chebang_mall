<?php
class ectools_api_payment_recharge{

    public $apiDescription = "订单支付请求支付网关";

    public function getParams()
    {
        $return['params'] = array(
            'pay_app_id' => ['type'=>'string','valid'=>'required', 'description'=>'支付方式', 'default'=>'', 'example'=>'alipay'],
            'platform' => ['type'=>'string','valid'=>'required', 'description'=>'来源平台（wap、pc）', 'default'=>'pc', 'example'=>'pc'],
            'money' => ['type'=>'string','valid'=>'required', 'description'=>'支付金额', 'default'=>'', 'example'=>'234.50'],
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'用户id', 'default'=>'', 'example'=>'1'],
            'user_name' => ['type'=>'string','valid'=>'required', 'description'=>'订单所属用户名', 'default'=>'', 'example'=>''],
            //'itemtype' => ['type'=>'string','valid'=>'', 'description'=>'商品类型', 'default'=>'', 'example'=>''],
        );
        return $return;
    }

    /**
     *
     * 生成支付单，并且跳转到支付页面
     *
     */
    public function doRecharge($params)
    {


        //生成paymentId
        $paymentId = $this->__genPaymentId($params['user_id']);

        $url = $params['platform'] == 'pc' ? serialize(['topc_ctl_member_deposit@rechargeResult', ['payment_id'=>$paymentId]]) : serialize(['topwap_ctl_member_deposit@rechargeResult', ['payment_id'=>$paymentId]]);

        $db = app::get('ectools')->database();
        $db->beginTransaction();
        try
        {
            $objMdlPayment = app::get('ectools')->model('payments');
            $payment = array(
                'payment_id' => $paymentId,
                'money' => $params['money'],
                'pay_app_id' => $params['pay_app_id'],
                'cur_money' => $params['money'],
                'status' => 'paying',
                'user_id' => $params['user_id'],
                'user_name' => $params['user_name'],
                'op_id' => $params['user_id'],
                'op_name' => $params['user_name'],
                'pay_type' => 'recharge',
                'created_time' => time(),
                'return_url' => $url,
            );
            $objMdlPayment->insert($payment);

            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        $newPayment = $objMdlPayment->getRow('*',['payment_id'=>$paymentId]);
        $newPayment['item_title'] = '预存款充值';
        if(config::get('app.debug'))
        {
            $newPayment['item_title'] = '[测试]'.$newPayment['item_title'];
        }

        $objPayment = kernel::single('ectools_pay');
        $result = $objPayment->generate($newPayment);

        return ['paymentId'=>$paymentId];
    }

    /**
     *
     * 生成一个PaymentId
     *
     * return $paymentId
     *
     */
    private function __genPaymentId($userId)
    {
        $objMdlPayment = app::get('ectools')->model('payments');

        do{
            $str = (string)(intval($userId) + 10000);
            $str = substr($str, strlen($str) - 4, strlen($str));
            $paymentId = time() . $str . rand(0, 9999);

            $row = $objMdlPayment->getRow('payment_id',array('payment_id'=>$paymentId));
        }while($row);

        return $paymentId;
    }

}


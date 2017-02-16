<?php
class ectools_api_payment_checkPayment{
    public $apiDescription = "检查支付单状态!";
    public function getParams()
    {
        $data['params'] = array(
            'payment_id' => ['type'=>'string','valid'=>'required', 'description'=>'支付单编号', 'default'=>'', 'example'=>''],
        );
        return $data;
    }
    public function checkPayment($params)
    {
        $objMdlPayments = app::get('ectools')->model('payments');
        $payment = $objMdlPayments->getRow('status', array('payment_id'=>$params['payment_id']));
        return $payment['status'];

    }

}



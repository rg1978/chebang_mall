<?php
class sysuser_api_user_deposit_refund{

    public $apiDescription = "平台退款至预存款";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'operator' => ['type'=>'string','valid'=>'required', 'description'=>'操作员','default'=>'','example'=>''],
            'fee' => ['type'=>'float', 'valid'=>'required|numeric|min:0', 'description'=>'消费金额', 'default'=>'', 'example'=>'100.2'],
            'memo' => ['type'=>'string', 'valid'=>'', 'description'=>'备注信息','default'=>'', 'example'=>'前端消费，订单号：1111111，支付单号：222222222'],
        );
        return $return;
    }

    public function doRefund($params)
    {

        //组织数据，userId
        $userId = $params['user_id'];

        //组织数据，operator
        $operator = $params['operator'];

        //组织数据，fee
        $fee = $params['fee'];

        //组织数据, memo
        $memo = $params['memo'];

        $deposit = kernel::single('sysuser_data_deposit_deposit')->add($userId, $operator, $fee, $memo);

        return ['result'=>true];
    }
}

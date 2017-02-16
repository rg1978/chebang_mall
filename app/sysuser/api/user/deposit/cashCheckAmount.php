<?php
class sysuser_api_user_deposit_cashCheckAmount
{
    public $apiDescription = "申请提现数据验证";

    public function getParams()
    {
        $return['params'] = array(
            'user_id'         => ['type'=>'int',       'valid'=>'required|numeric', 'title'=>'用户id',     'desc'=>'用户id'],
            'amount'          => ['type'=>'float',     'valid'=>'required',     'title'=>'金额',       'desc'=>'用户提现的金额'],
        );
        return $return;
    }


    public function check($params)
    {
        $userId = $params['user_id'];
        $amount = $params['amount'];
        return kernel::single('sysuser_data_deposit_cash')->checkAmount($userId, $amount);
    }
}


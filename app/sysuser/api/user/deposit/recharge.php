<?php
class sysuser_api_user_deposit_recharge{
    public $apiDescription = "用户充值接口";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'fee' => ['type'=>'float', 'valid'=>'required|numeric|min:0', 'description'=>'消费金额', 'default'=>'', 'example'=>'100.2'],
            'memo' => ['type'=>'string', 'valid'=>'', 'description'=>'备注信息','default'=>'', 'example'=>'充值，支付单号：222222222'],
        );
        return $return;
    }

    public function doRecharge($params)
    {

        //组织数据，userId
        $userId = $params['user_id'];
        if(!$params['user_id'])
        {
            $userId = $params['oauth']['account_id'];
        }

        //组织数据，userName
        $userName = app::get('sysuser')->_('用户');

        //组织数据，fee
        $fee = $params['fee'];

        //组织数据, memo
        $memo = $params['memo'];

        $deposit = kernel::single('sysuser_data_deposit_deposit')->add($userId, $userName, $fee, $memo);

        return ['result'=>true];
    }
}


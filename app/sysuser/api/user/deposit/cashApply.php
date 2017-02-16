<?php
class sysuser_api_user_deposit_cashApply
{
    public $apiDescription = "申请提现";

    public function getParams()
    {
        $return['params'] = array(
            'user_id'         => ['type'=>'int',       'valid'=>'numeric|required', 'title'=>'用户id',     'desc'=>'用户id'],
            'amount'          => ['type'=>'float',     'valid'=>'required',     'title'=>'金额',       'desc'=>'用户提现的金额'],
            'bank_card_id'    => ['type'=>'string',    'valid'=>'required',     'title'=>'银行卡号',   'desc'=>'提现的银行卡卡号'],
            'bank_name'       => ['type'=>'string',    'valid'=>'required',     'title'=>'银行名称',   'desc'=>'银行名称，例如中国工商银行'],
            'bank_card_owner' => ['type'=>'string',    'valid'=>'required',     'title'=>'持卡人姓名', 'desc'=>'持卡人姓名'],
            'password'        => ['type'=>'string',    'valid'=>'required',     'title'=>'支付密码',   'desc'=>'预存款支付密码'],
        );
        return $return;
    }

    /**
     * @return string cash_id 提现单号
     *
     */
    public function apply($params)
    {
        $userId = $params['user_id'];
        $amount = $params['amount'];
        $bankCardId = $params['bank_card_id'];
        $bankName = $params['bank_name'];
        $bankCardOwner = $params['bank_card_owner'];
        $password = $params['password'];

        $db = app::get('sysuser')->database();
        $db->beginTransaction();
        try{
            $cashId = kernel::single('sysuser_data_deposit_cash')->applyCash($userId, $amount, $bankCardId, $bankName, $bankCardOwner, $password);
        }catch(Exception $e){
            $db->rollback();
            throw $e;
        }
        $db->commit();
        return ['cash_id'=>$cashId];
    }

}

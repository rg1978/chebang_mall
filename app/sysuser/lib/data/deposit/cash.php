<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_deposit_cash
{

    //获取一些配置信息，比如单次提现多少，剩余多少次
    public function getConf($userId)
    {
        if($userId)
        {
            $config['depositCashLimitAmount'] = $this->__getCashLimitAmount($userId);
            $config['depositCashLimitTimesLeft'] = kernel::single('sysuser_data_deposit_cashLocker')->getTimesLeft($userId);
        }

        $config['depositCashLimitTimesDefault'] = kernel::single('sysuser_data_deposit_cashLocker')->getLimitTimes();
        $config['depositCash'] = app::get('sysconf')->getConf("user.deposit.cash");

        return $config;
    }

    public function getList($fields, $userId, $page, $rowNum)
    {
        $objDepositCash = app::get('sysuser')->model('user_deposit_cash');
        $count = $objDepositCash->count(['user_id'=>$userId]);

        $rowNum = $rowNum > 20 ? 20 : $rowNum;
        $rowNum = $rowNum < 1  ?  1 : $rowNum;
        $pageTotal = ceil($count/$rowNum);
        $page = ($pageTotal < $page) ? $pageTotal : $page;
        $offset = ($page - 1) * $rowNum;

        $logs = $objDepositCash->getList($fields, ['user_id'=>$userId], $offset, $rowNum, 'cash_id DESC');

        return [$logs, $count];
    }

    //验证提现金额以及会员当日提现次数
    public function checkAmount($userId, $amount)
    {
        try{
            $this->__checkAmount($userId, $amount);
        }catch(LogicException $e){
            return ['result'=>false, 'msg'=>$e->getMessage()];
        }
        return ['result'=>true];
    }

    //申请提现
    public function applyCash($userId, $amount, $bankCardId, $bankName, $bankCardOwner, $password)
    {
        $this->__checkAmount($userId, $amount);
        kernel::single('sysuser_data_deposit_password')->checkPassword($userId, $password);
        kernel::single('sysuser_data_deposit_cashLocker')->runCash($userId);
        kernel::single('sysuser_data_deposit_deposit')->dedect($userId, '用户', $amount, '提现');
        return $this->createCash($userId, $amount, $bankCardId, $bankName, $bankCardOwner);
    }

    //创建提现单
    public function createCash($userId, $amount, $bankCardId, $bankName, $bankCardOwner)
    {
        $time = time();
        $cashId = $this->__genCashId($userId, $time);
        $userDepostCash = [
            'cash_id' => $cashId,
            'user_id' => $userId,
            'create_time' => $time,
            'amount' => $amount,
            'bank_card_id' => $bankCardId,
            'bank_name' => $bankName,
            'bank_card_owner' => $bankCardOwner,
            'status' => 'TO_VERIFY',
        ];
        $result = app::get('sysuser')->model('user_deposit_cash')->save($userDepostCash);

        if($result)
            return $cashId;
        else throw new RuntimeException(app::get('sysuser')->_('提现单保存失败'));
        return false;
    }

    //审核提现单
    public function verifyCash($cashId, $flag = false)
    {
        $userDepostCash['cash_id'] = $cashId;
        if($flag)
        {
            $userDepostCash['status'] = 'VERIFIED';
        } else {
            $userDepostCash['status'] = 'DENIED';
        }
        $cash = app::get('sysuser')->model('user_deposit_cash')->getRow('user_id,amount', ['cash_id'=>$cashId]);

        $db = app::get('sysuser')->database();
        $result = $db->executeUpdate('UPDATE sysuser_user_deposit_cash SET status = ? WHERE cash_id = ? and status = ?', [$userDepostCash['status'], $cashId, 'TO_VERIFY']);
        if(!$result)
        {
            throw new LogicException(app::get('sysuser')->_('提现单状态异常，请刷新页面后重新审核'));
        }

        $userId = $cash['user_id'];
        $amount = $cash['amount'];
        if(!$flag)
            kernel::single('sysuser_data_deposit_deposit')->add($userId, '管理员', $amount, '提现驳回，资金回流');

        return $result;
    }

    //财务完成提现
    public function completeCash($cashId, $serialId, $executor)
    {
        $userDepostCash['cash_id'] = $cashId;
        $userDepostCash['serial_id'] = $serialId;
        $userDepostCash['status'] = 'COMPELETE';
        $userDepostCash['executor'] = $executor;
        return app::get('sysuser')->model('user_deposit_cash')->save($userDepostCash);
    }

    private function __genCashId($userId, $time)
    {
        $count = 0;
        while(1)
        {
            $cashId = $time . substr('000' . $userId, -4) . rand(1000, 9999);

            if(app::get('sysuser')->model('user_deposit_cash')->count(['cash_id'=>$cashId]) == 0)
                return $cashId;

            if($count > 5)
            {
                throw new RuntimeException(app::get('sysuser')->_('系统繁忙，请稍后再试'));
            }
            $count ++;
        }
    }

    private function __checkAmount($userId, $amount)
    {
        kernel::single('sysuser_data_deposit_cashLocker')->checkLock($userId);

        if($amount > $this->__getCashLimitAmount($userId))
            throw new RuntimeException(app::get('sysuser')->_('金额过大'));

        $userDeposit = kernel::single('sysuser_data_deposit_deposit')->get($userId);
        if($userDeposit < $amount)
            throw new RuntimeException(app::get('sysuser')->_('余额不足'));

        return true;
    }

    private function __getCashLimitAmount($userId)
    {
        return (int)app::get('sysconf')->getConf("user.deposit.cash.limit.amount");

    }
}


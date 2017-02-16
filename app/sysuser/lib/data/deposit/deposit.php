<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_deposit_deposit
{

    /**
     * 获取会员的预存款数值
     *
     * @params userId int 会员id
     *
     * @return deposit fload 会员的预存款
     *
     */
    public function get($userId)
    {
        $deposit = app::get('sysuser')->model('user_deposit')->getRow('deposit', ['user_id'=>$userId]);
        $deposit = $deposit['deposit'] ? $deposit['deposit'] : 0;
        return $deposit;
    }

    /**
     * 变更预存款接口（目前仅用于后台调整预存款的数值，可增可减）
     *
     * $params userId int 会员id
     * @params operator string 操作员
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function update($userId, $operator, $fee, $memo)
    {
        $money = abs($fee);
        if($fee > 0)
        {
            return $this->add($userId, $operator, $money, $memo);
        }
        elseif($fee < 0)
        {
            return $this->dedect($userId, $operator, $money, $memo);
        }
        else
        {
            return true;
        }
    }

    /**
     * 会员充值接口
     *
     * @params userId int 会员id
     * @params operator string 操作员
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function add($userId, $operator, $fee, $memo)
    {
        $this->checkUserId($userId);
        logger::info("User deposit add : [userId:{$userId},operator:{$operator},fee:{$fee},memo:${memo}]");

        $db = app::get('sysuser')->database();
        $result = $db->executeUpdate('UPDATE sysuser_user_deposit SET deposit = deposit + ? WHERE user_id = ?', [$fee, $userId]);
        if(!$result)
        {
            $userDepost = ['user_id' => $userId, 'deposit' => $fee];
            app::get('sysuser')->model('user_deposit')->save($userDepost);
        }

        kernel::single('sysuser_data_deposit_log')->addLog($userId, $operator, $fee, $memo, 'add');

        return true;
    }

    /**
     * 会员扣费接口
     *
     * @params userId int 会员id
     * @params operator string 操作员
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function dedect($userId, $operator, $fee, $memo)
    {
        $this->checkUser($userId);
        logger::info("User deposit dedect : [userId:{$userId},operator:{$operator},fee:{$fee},memo:${memo}]");

        $db = app::get('sysuser')->database();
        $result = $db->executeUpdate('UPDATE sysuser_user_deposit SET deposit = deposit - ? WHERE user_id = ? and deposit >= ?', [$fee, $userId, $fee]);
        if(!$result)
        {
            logger::info("User deposit dedect failed : [userId:{$userId},operator:{$operator},fee:{$fee},memo:${memo}]");
            throw new LogicException(app::get('sysuser')->_('预存款余额不足'), $userId.'001');
        }

        kernel::single('sysuser_data_deposit_log')->addLog($userId, $operator, $fee, $memo, 'expense');

        return true;
    }

    private function checkUser($userId)
    {
        $this->checkUserId($userId);
        return true;
    }

    private function checkUserId($userId)
    {
        if(! $userId > 0)
            throw new LogicException('会员id格式不正确!');

        return true;
    }
}


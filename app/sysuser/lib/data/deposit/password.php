<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_deposit_password
{

    /**
     *
     * 判断会员是否有密码
     *
     * @params int userId 会员id
     * @return bool true有会员密码，false无会员密码
     */
    public function hasPassword($userId)
    {
        $deposit = app::get('sysuser')->model('user_deposit')->getRow('password', ['user_id'=>$userId]);
        $password = $deposit['password'];
        if($password == '')
            return false;
        return true;
    }

    /**
     *
     * 检查会员的密码是否正确
     *
     * @params int userId 会员id
     * @password string password 会员密码
     * @return bool true密码正确，false密码错误
     *
     */
    public function checkPassword($userId, $password)
    {
        //检查数据安全
        if( empty($password))
        {
            throw new \LogicException(app::get('sysuser')->_('请输入密码!'));
            return false;
        }

        $deposit = app::get('sysuser')->model('user_deposit')->getRow('password', ['user_id'=>$userId]);

        $passwordLocker = sysuser_data_passwordLocker::instance('deposit.password');

        $passwordLocker->checkLock($userId);

        if(!pam_encrypt::check($password, $deposit['password']))
        {
            $passwordLocker->tryVerify($userId);

            throw new \LogicException(app::get('sysuser')->_('密码填写错误！'));
        }

        $passwordLocker = sysuser_data_passwordLocker::instance('deposit.password');
        $passwordLocker->clean($userId);

        return true;
    }

    /**
     * 设置密码
     *
     * @params int userId 会员Id
     * @password string password 会员密码
     * @return bool true密码成功
     *
     */
    public function setPassword($userId, $password)
    {
        $password = pam_encrypt::make($password);

        $userDeposit = [
            'user_id' => $userId,
            'password' => $password,
            ];
        $passwordLocker = sysuser_data_passwordLocker::instance('deposit.password');
        $passwordLocker->clean($userId);
        $flag = app::get('sysuser')->model('user_deposit')->save($userDeposit);
        if(!$flag)
        {
            throw new RuntimeException(app::get('预存款密码保存失败!'));
        }
        return true;
    }

    /**
     *
     * 修改密码接口
     *
     * @params int userId 会员Id
     * @password string password 会员密码
     * @return bool true密码成功
     *
     */
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $this->checkPassword($userId, $oldPassword);
        $this->setPassword($userId, $newPassword);
        return true;
    }

    /**
     *
     * 清空密码接口
     *
     * @params int userId 会员Id
     * @return bool true密码成功
     *
     */
    public function resetPassword($userId)
    {
        if(!$userId > 0)
        {
            throw new RuntimeException(app::get('用户Id格式错误!'));
        }
        $userDeposit = [
            'user_id' => $userId,
            'password' => '',
            ];
        $flag = app::get('sysuser')->model('user_deposit')->save($userDeposit);
        if(!$flag)
        {
            throw new RuntimeException(app::get('预存款密码保存失败!'));
        }
        return true;
    }

    /**
     * 验证登录密码和支付密码是否一致
     * @param int $userId
     * @param string $accoutPassword 用户输入的登录密码
     * @return bool
     * */
    public function checkLogpwdWithDepositpwd($userId, $accoutPassword)
    {
        // 验证支付密码和登录密码是否一致
        $result = app::get('sysuser')->model('user_deposit')->getRow('password',array('user_id'=>$userId));
        if(pam_encrypt::check($accoutPassword, $result['password']))
        {
            throw new \LogicException(app::get('sysuser')->_('登录密码不能与支付密码一致！'));
        }

        return true;
    }

}



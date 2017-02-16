<?php
/**
 * @brief 商城账号
 */
class sysuser_ctl_admin_user extends desktop_controller {


    function __construct($app){
        parent::__construct($app);
        $this->pamUserModel = app::get('sysuser')->model('account');
        $this->sysUserModel = app::get('sysuser')->model('user');
    }
    /**
     * @brief  商家账号列表
     *
     * @return
     */
    public function index()
    {
        return $this->finder('sysuser_mdl_user',array(
            'title' => app::get('sysuser')->_('商城会员列表'),
            'use_buildin_filter' => true,
            'use_buildin_delete' => true,
            'actions'=>array(
                array(
                    'label'=>app::get('syscategory')->_('为选中的会员标记标签'),
                    'submit'=>'?app=sysuser&ctl=admin_tag&act=bindTag','target'=>'dialog::{title:\''.app::get('syscategory')->_('添加标签').'\',width:680,height:350}',
                    'href'=>'javascript:void(0)',
                ),
            )
        ));
    }

    public function license()
    {
        if( $_POST['license'] )
        {
            $this->begin();
            app::get('sysuser')->setConf('sysuser.register.setting_user_license',$_POST['license']);
            $this->end(true, app::get('sysuser')->_('当前配置修改成功！'));
        }
        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_user_license');
        return $this->page('sysuser/license.html', $pagedata);
    }

    /**
     * @brief  前台会员信息修改
     *
     * @return
     */
    public function updateDeposit()
    {
        $this->begin();
        $data = input::get();
        $userId  = $data['user_id'];
        $operator = $this->user->get_name();
        $deposit = $data['deposit'];
        $memo    = $data['memo'] ? $data['memo'] : (($deposit < 0) ? "管理员扣减" : "管理员充值");

        try
        {
            $flag = kernel::single('sysuser_data_deposit_deposit')->update($userId, $operator, $deposit, $memo );
            $this->adminlog("修改会员预存款[USER_ID:{$userId}]", 1);
            return $this->end(true, app::get('sysuser')->_('预存款修改成功'));
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员预存款[USER_ID:{$userId}]", 0);
            $msg = $e->getMessage();
            return $this->end(false, $msg);
        }
    }

    /**
     * @brief  前台会员信息修改
     *
     * @return
     */
    public function editUserInfo($userId)
    {

        $sysInfo = kernel::single('sysuser_passport')->memInfo($userId);

        if($sysInfo['sex']==1)
        {
            $sysInfo['sex']='male';
        }
        else
        {
            $sysInfo['sex']='female';
        }
        $data = array(
            'user_id'=>$sysInfo['userId'],
            'name'=>$sysInfo['name'],
            'sex'=>$sysInfo['sex'],
            'birthday'=>$sysInfo['birthday'],
            'reg_ip'=>$sysInfo['reg_ip'],
            'regtime'=>$sysInfo['regtime'],
            'login_account'=>$sysInfo['login_account'],
            'email'=>$sysInfo['email'],
            'mobile'=>$sysInfo['mobile'],
        );

        $pagedata['data'] = $data;
        return $this->page('sysuser/admin/editinfo.html', $pagedata);
    }

    /**
     * @brief  前台会员信息保存
     *
     * @return
     */
    public function saveUserInfo()
    {
        try
        {
            $data = $_POST;
            kernel::single('sysuser_passport')->saveInfo($data);
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('修改成功');

        return $this->splash('success',null,$msg);
    }

    /**
     * @brief  前台会员密码修改
     *
     * @return
     */
    public function updatePwd()
    {
        try
        {
            $data = $_POST;
            $params = array(
                'type' =>'reset',
                'new_pwd' =>$data['login_password'],
                'confirm_pwd' =>$data['psw_confirm'],
                'user_id' =>$data['user_id'],
            );

            kernel::single('sysuser_passport')->modifyPwd($params);
            $this->adminlog("修改会员密码[USER_ID:{$data['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员密码[USER_ID:{$data['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('修改成功');

        return $this->splash('success',null,$msg);
    }

    public function changePoint()
    {
        $data = input::get('point');

        if(!$data['user_id'])
        {
            $msg = app::get('sysuser')->_('会员参数错误');
            return $this->splash('error',null,$msg);
        }
        $objMdlUserPoints = app::get('sysuser')->model('user_points');
        $points = $objMdlUserPoints->getRow('point_count',array('user_id'=>$data['user_id']));
        if(iconv_strlen($data['modify_point']) > 10)
        {
            $msg = app::get('sysuser')->_('积分值长度过长');
            return $this->splash('error',null,$msg);
        }

        if($data['modify_point'] < 0 && abs($data['modify_point']) > $points['point_count'])
        {
            $msg = app::get('sysuser')->_('会员积分不足');
            return $this->splash('error',null,$msg);
        }
        //平台修改积分
        $objPoints = kernel::single('sysuser_data_user_points');
        try
        {
            //平台操作会员积分时，先处理会员的积分过期
            $result = $objPoints->pointExpiredCount($data['user_id']);
            if(!$result)
            {
                throw new Exception('会员积分过期处理失败');
            }

            $result = $objPoints->changePoint($data);
            if(!$result)
            {
                throw new Exception('会员积分更改失败');
            }
            $this->adminlog("更改会员积分[USER_ID:{$data['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("更改会员积分[USER_ID:{$data['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $msg = app::get('sysuser')->_('修改成功');
        return $this->splash('success',null,$msg);
    }

    /**
     * @brief  前台会员密码修改
     *
     * @return
     */
    public function resetDepositPassword()
    {
        try{
            $userId = $_GET['user_id'];
            if(!$userId > 0 )
                throw new LogicException('用户Id格式错误');

            $deposit = kernel::single('sysuser_data_deposit_password')->resetPassword($userId);
            $this->adminlog("重置会员支付密码[USER_ID:{$data['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("重置会员支付密码[USER_ID:{$data['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('重置成功');
        return $this->splash('success',null,$msg);
    }

    /**
     * @brief  提现单列表
     *
     * @return
     */
    public function cash()
    {
        return $this->finder('sysuser_mdl_user_deposit_cash',array(
            'title' => app::get('sysuser')->_('预存款提现单列表'),
            'use_buildin_filter' => true,
            'use_buildin_delete' => false,
        ));
    }

    /**
     * @brief  提现单列表
     *
     * @return
     */
    public function cashVerifyPage()
    {

        return ;
    }

    /**
     * @brief  提现单列表
     *
     * @return
     */
    public function cashVerify()
    {
        $cashId = input::get('cash_id');
        $allow = input::get('allow');
        $this->begin();
        try{

            if($allow==1)
                $result = kernel::single('sysuser_data_deposit_cash')->verifyCash($cashId, true);
            else
                $result = kernel::single('sysuser_data_deposit_cash')->verifyCash($cashId, false);

            if($result)
            {
                $this->adminlog("预存款提现审核[CASH_ID:{$cashId},ALLOW:{$allow}]", 1);
                return $this->end(true, app::get('sysuser')->_('审核结果保存成功！'), '?app=sysuser&ctl=admin_user&act=cash');
              //return $this->end(true, app::get('sysuser')->_('审核结果保存成功！'),['app'=>'sysuser', 'ctl'=>'admin_user', 'act'=>'cash']);
            }
            else
                throw new RuntimeException(app::get('sysuser')->_('审核结果保存失败'));

        } catch(Exception $e) {
                $this->adminlog("预存款提现审核[CASH_ID:{$cashId},ALLOW:{$allow}]", 0);
                return $this->end(false, $e->getMessage(), '?app=sysuser&ctl=admin_user&act=cash');
       }
    }

    /**
     * @brief  提现单列表
     *
     * @return
     */
    public function cashCompeletePage()
    {
        $cashId = input::get('cash_id');
        $pagedata['cashId'] = $cashId;

        return $this->page('sysuser/admin/user/cash/compelete.html', $pagedata);
    }

    /**
     * @brief  提现单列表
     *
     * @return
     */
    public function cashCompelete()
    {
        $cashId = input::get('cash_id');
        $serialId = input::get('serial_id');
        $executor = input::get('executor');

        try{
            $result = kernel::single('sysuser_data_deposit_cash')->completeCash($cashId, $serialId, $executor);
            if($result)
            {
                $this->adminlog("预存款提现完成[CASH_ID:{$cashId},SERIAL_ID:{$serialId},EXECUTOR:{$executor}]", 1);
                return $this->splash('success', '#', app::get('sysuser')->_('流水号保存成功'));
            }
            else
                throw new RuntimeException(app::get('sysuser')->_('流水号保存失败'));
        } catch(Exception $e) {
            $this->adminlog("预存款提现完成[CASH_ID:{$cashId},SERIAL_ID:{$serialId},EXECUTOR:{$executor}]", 0);
            return $this->splash('error',null, $e->getMessage());
        }
    }
}

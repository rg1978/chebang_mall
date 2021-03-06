<?php

/**
 * deposit.php 会员预存款
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topwap_ctl_member_deposit extends topwap_ctl_member {

    const CHANGE_TYPE = 'change';
    public $limit = 10;
    protected $payment_icon = array(
            'wapupacp' => 'bbc-icon-unipay pay-style-unipay',
            'deposit' => 'bbc-icon-qianbao pay-style-qianbao',
            'malipay' => 'bbc-icon-zhifubao pay-style-zhifubao',
            'wxpayjsapi' => 'bbc-icon-weixin pay-style-weixin'
    );
    protected $cachePaymentIdKey = 'wap:pay.wait.payid';

    public function index()
    {
        $userId = userAuth::id();
        $deposit = app::get('topwap')->rpcCall('user.deposit.getInfo', ['user_id'=>$userId, 'with_log'=>false]);
        $pagedata['deposit'] = $deposit;
        $pagedata['title'] = app::get('topwap')->_('我的预存款');

        return $this->page('topwap/member/deposit/index.html', $pagedata);
    }

    public function view()
    {
        $deposit = $this->__getdepositLog();

        $pagedata['pagers'] = array(
                'link'=>'',
                'current'=>$deposit['page'],
                'total'=>ceil($deposit['count'] / $this->limit),
        );
        $pagedata['deposit'] = $deposit;
        $pagedata['title'] = app::get('topwap')->_('预存款明细');

        return $this->page('topwap/member/deposit/detail.html',$pagedata);
    }

    public function ajaxDepositLog()
    {
        try {
            $pagedata['deposit'] = $this->__getdepositLog();
            $data['html'] = view::make('topwap/member/deposit/list.html',$pagedata)->render();
            $data['success'] = true;
            $data['pages'] = $pagedata['deposit']['pages'];
        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage(), true);
        }

        return response::json($data);
    }

    public function rechargeSubmit()
    {
        $pagedata['title'] = app::get('topwap')->_('预存款充值');

        return $this->page('topwap/member/deposit/recharge_form.html',$pagedata);
    }

    public function rechargePay()
    {
        $amount = input::get('amount');

        try{
            $this->checkoutAmount($amount);
        }
        catch(Exception $e)
        {
            return $this->splash('error',null,$e->getMessage());
        }

        $payType['platform'] = 'iswap';
        $payments = app::get('topwap')->rpcCall('payment.get.list',$payType,'buyer');
        foreach($payments as $key=>$payment)
        {
            if($payment['app_id'] == 'deposit')
            {
                unset($payments[$key]);
                continue;
            }

            // 微信支付
            if(in_array($payment['app_id'], ['wxpayjsapi']))
            {
                if(!kernel::single('topwap_wechat_wechat')->from_weixin())
                {
                    unset($payments[$key]);
                    continue;
                }

                $payInfo = app::get('topwap')->rpcCall('payment.get.conf', ['app_id' => 'wxpayjsapi']);
                $wxAppId = $payInfo['setting']['appId'];
                $wxAppsecret = $payInfo['setting']['Appsecret'];
                if(!input::get('code'))
                {
                    $url = url::action('topwap_ctl_member_deposit@rechargePay',input::get());
                    kernel::single('topwap_wechat_wechat')->get_code($wxAppId, $url);
                }
                else
                {
                    $code = input::get('code');
                    $openid = kernel::single('topwap_wechat_wechat')->get_openid_by_code($wxAppId, $wxAppsecret, $code);
                    if($openid == null)
                        $this->splash('failed', 'back',  app::get('topwap')->_('获取openid失败'));
                    $pagedata['openid'] = $openid;
                }
            }
        }

        $pagedata['amount']   = $amount;
        $pagedata['payments'] = $payments;
        $pagedata['paymentIcon'] = $this->payment_icon;
        $pagedata['title'] = app::get('topwap')->_('选择支付方式');

        $this->action_view = "deposit/recharge_pay.html";
        return $this->page('topwap/member/deposit/recharge_pay.html',$pagedata);
    }

    public function doRecharge()
    {
        $payment['user_id'] = userAuth::id();
        $payment['user_name'] = userAuth::getLoginName();

        $payment['money'] = input::get('amount');
        try{

            $this->checkoutAmount($payment['money']);
            $payment['pay_app_id'] = input::get('pay_app_id');
            $payment['platform'] = 'wap';

            if($payment['pay_app_id'] == '')
                throw new LogicException('请选择支付方式');
            if($payment['pay_app_id'] == 'deposit')
                throw new LogicException('充值方式不可使用预存款!');

            $result = app::get('topwap')->rpcCall('payment.deposit.recharge', $payment);
            $paymentId = $result['paymentId'];

        }
        catch(Exception $e)
        {
            return $this->splash('error',null,$e->getMessage());
        }

        return redirect::action('topwap_ctl_member_deposit@rechargeResult', ['payment_id'=>$paymentId]);
    }

    // 支付结果
    public function rechargeResult()
    {
        $paymentId = input::get('payment_id');

        $payment = app::get('topwap')->rpcCall('payment.bill.get', ['payment_id'=>$paymentId, 'fields'=>'status,cur_money']);
        $pagedata = $payment;
        if($payment['status'] == 'succ')
        {
            $pagedata['title'] = app::get('topwap')->_('充值成功');
            return $this->page('topwap/member/deposit/recharge_success.html',$pagedata);
        }
        else
        {
            $pagedata['title'] = app::get('topwap')->_('充值失败');
            return $this->page('topwap/member/deposit/recharge_failed.html',$pagedata);
        }
    }

    // 安全中心预存款密码
    public function depositPwd()
    {
        // 获取从支付页面过来的支付单号
        $input = input::get();
        if(isset($input['payment_id']))
        {
            $userId = userAuth::id();
            cache::store('session')->put($this->cachePaymentIdKey.'-'.$userId, $input['payment_id'], 30);
        }

        $pagedata['title'] = app::get('topwap')->_('预存款支付密码');
        $pagedata['hasDepositPassword'] = app::get('topwap')->rpcCall('user.deposit.password.has', ['user_id'=>userAuth::id()]);

        return $this->page('topwap/member/deposit/payment_password.html', $pagedata);
    }
    // 修改支付密码
    public function modifyPassword()
    {
        $request = input::get();
        if(isset($request['type']) && $request['type'] == self::CHANGE_TYPE)
        {
            // 判断是否进行了预存款旧密码验证
            $setDepositPasswordFlagCheckLogin = $this->getSessionValue('setDepositPasswordFlagCheckOldpay', false);
            if(!$setDepositPasswordFlagCheckLogin) return redirect::action('topwap_ctl_member_deposit@checkOldpayPwd');
        }
        else
        {
            // 判断是否进行了登录验证
            $setDepositPasswordFlagCheckLogin = $this->getSessionValue('setDepositPasswordFlagCheckLogin', false);
            if(!$setDepositPasswordFlagCheckLogin) return redirect::action('topwap_ctl_member_deposit@checkLoginpwd');
        }


        $pagedata['title'] = app::get('topwap')->_('设置支付密码');
        $pagedata['type'] = isset($request['type']) ? $request['type'] : '';
        return $this->page('topwap/member/deposit/payment_password_setting.html', $pagedata);
    }

    // 验证预存款旧密码
    public function checkOldpayPwd()
    {
        $pagedata['title'] = app::get('topwap')->_('验证原支付密码');

        return $this->page('topwap/member/deposit/payment_password_verify_oldpay.html', $pagedata);
    }

    public function doCheckOldpayPwd()
    {
        $oldPassword = input::get('old_pwd');
        $msg = '';
        try {
            if(!$oldPassword)
            {
                throw new LogicException(app::get('topwap')->_('请填写原支付密码'));
            }
            $userId = userAuth::id();
            $requestParams = ['user_id'=>$userId, 'old_password'=>$oldPassword];
            // 开始验证密码
            $resutl = app::get('topwap')->rpcCall('user.check.deposit.oldpwd', $requestParams, 'buyer');
            $this->setSessionValue('setDepositPasswordFlagCheckOldpay', true);
            $url = url::action('topwap_ctl_member_deposit@modifyPassword', array('type' => self::CHANGE_TYPE));
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }
        // 根据前端要求将成功提示置空
        // $msg = app::get('topwap')->_('密码验证成功');
        return $this->splash('success', $url, $msg, true);
    }
    // 验证登录密码
    public function checkLoginpwd()
    {
        $pagedata['title'] = app::get('topwap')->_('验证登录密码');

        return $this->page('topwap/member/deposit/payment_password_verification.html', $pagedata);
    }

    public function doCheckLoginPwd()
    {
        $login_pwd = input::get('login_pwd');
        $msg = '';
        try {
            if(!$login_pwd)
            {
                throw new LogicException(app::get('topwap')->_('请填写您的原密码'));
            }
            // 开始验证登录密码
            $resutl = app::get('topwap')->rpcCall('user.login.pwd.check', ['password'=> $login_pwd], 'buyer');
            $this->setSessionValue('setDepositPasswordFlagCheckLogin', true);
            $url = url::action('topwap_ctl_member_deposit@modifyPassword');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }
        // 根据前端要求将成功提示置空
        // $msg = app::get('topwap')->_('密码验证成功');
        return $this->splash('success', $url, $msg, true);
    }

    public function doModifyPassword()
    {
        try {
            $userId = userAuth::id();
            // 查看是否已设置过密码
            $depositPasswordFlag = app::get('topwap')->rpcCall('user.deposit.password.has', ['user_id'=>$userId]);
            $depositPasswordFlag = $depositPasswordFlag['result'];
            
            $request = input::get();
            $newPassword = input::get('new_password');
            $confirm_password = input::get('confirm_password');

            if($newPassword != $confirm_password)
                throw new LogicException(app::get('topwap')->_('两次输入密码不一致！请确认'));
            // 验证密码格式
            $this->checkPassword($newPassword);
            // 进行密码入库操作
            $flag = false;
            if(isset($request['type']) && $request['type'] == self::CHANGE_TYPE && $depositPasswordFlag)
            {
                $flag = $this->getSessionValue('setDepositPasswordFlagCheckOldpay', false);
            }
            else
            {
                $flag = $this->getSessionValue('setDepositPasswordFlagCheckLogin', false);
                if($depositPasswordFlag)
                {
                    $flag = false;
                }
            }

            if(!$flag)
                throw new LogicException(app::get('topwap')->_('密码验证已失效'));

            $requestParams = ['user_id'=>$userId, 'password'=>$newPassword];
            app::get('topwap')->rpcCall('user.deposit.password.set', $requestParams);

            if(isset($request['type']) && $request['type'] == self::CHANGE_TYPE)
            {
                $this->setSessionValue('setDepositPasswordFlagCheckOldpay', false);
            }
            else
            {
                $this->setSessionValue('setDepositPasswordFlagCheckLogin', false);
            }

        } catch (Exception $e) {
            return $this->splash('error', null, $e->getMessage());
        }

        // 生成跳转url，判断是否有支付单号
        $paymentId = cache::store('session')->pull($this->cachePaymentIdKey.'-'.$userId);
        if($paymentId)
        {
            $url = url::action('topwap_ctl_paycenter@index', ['payment_id' => $paymentId]);
        }
        else
        {
            $url = url::action('topwap_ctl_member@index');
        }

        $msg = app::get('topwap')->_('设置成功');
        return $this->splash('success', $url, $msg, true);
    }

    // 忘记密码
    public function forgetPassword()
    {
        $pagedata['title'] = app::get('topwap')->_('找回支付密码');
        // 判断会员是否绑定了手机
        $data = userAuth::getUserInfo();
        if(empty($data['mobile']))
        {
            return $this->page('topwap/member/deposit/forget_nomoblie.html', $pagedata);
        }
        $data['mobile_start'] = substr_replace($data['mobile'], '*****', 3, 5);
        $pagedata['data'] = $data;

        return $this->page('topwap/member/deposit/forget_password.html', $pagedata);
    }

    // 验证手机和验证码
    public function forgetPasswordSetPassword()
    {
        $postData = input::get();
        $vcode = $postData['vcode'];
        $loginName = $postData['uname'];
        $sendType = $postData['type'];
        $message = '';
        try
        {
            $vcodeData=userVcode::verify($vcode,$loginName,$sendType);
            if(!$vcodeData)
            {
                throw new LogicException(app::get('topwap')->_('验证码错误'));
            }
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
            return $this->splash('error',null,$message, true);
        }

        $this->setSessionValue('setDepositPasswordFlagCheckOldpay', true);
        $url = url::action('topwap_ctl_member_deposit@modifyPassword', ['type'=>self::CHANGE_TYPE]);
        // 根据前端要求将成功提示置空
        // $message= app::get('topwap')->_('验证成功');
        return $this->splash('success', $url, $message, true);
    }

    // 预存款短信验证码
    public function forgetPasswordSendVcode()
    {

        $postData = utils::_filter_input(input::get());
        $validator = validator::make(
            [$postData['uname']],['required'],['您的手机号不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }

        $valid = validator::make(
                [$postData['verifycode']],['required']
        );
        if($valid->fails())
        {
            return $this->splash('error',null,"图片验证码不能为空!");
        }
        if(!base_vcode::verify($postData['verifycodekey'],$postData['verifycode']))
        {
            return $this->splash('error',null,"图片验证码错误!");
        }

        try
        {
            $this->passport->sendVcode($postData['uname'],$postData['type']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        return $this->splash('success',null,"验证码发送成功");


    }


    private function checkPassword($newPassword)
    {
        $a = 0;
        if(preg_match("/(?=.*[0-9])[a-zA-Z0-9]{6,20}/", $newPassword))
            $a += 1;
        if(preg_match("/(?=.*[a-z])[a-zA-Z0-9]{6,20}/", $newPassword))
            $a += 1;
        if(preg_match("/(?=.*[A-Z])[a-zA-Z0-9]{6,20}/", $newPassword))
            $a += 1;

        if($a >= 2)
            return true;

        throw new LogicException('密码格式错误,请参考密码规则');
    }

    private function setSessionValue($key, $value)
    {
        $userId = userAuth::id();
        $key = $key.$userId;
        return cache::store('session')->put($key, $value, 5);
    }

    private function getSessionValue($key, $default)
    {
        $userId = userAuth::id();
        $key = $key.$userId;
        $value = cache::store('session')->get($key, $default);
        return $value;
    }

    private function __getdepositLog()
    {
        $userId = userAuth::id();
        $page = input::get('pages') ? input::get('pages') : 1;
        $deposit = app::get('topwap')->rpcCall('user.deposit.getInfo', ['user_id'=>$userId, 'with_log'=>'true', 'page'=>$page, 'row_num'=>$this->limit]);
        $deposit['pages'] = $page;
        $total=ceil($deposit['count'] / $this->limit);
        if($total<$page) return array();
        return $deposit;
    }

    private function checkoutAmount($amount)
    {

        if( !is_numeric($amount) )
            throw new LogicException('充值金额必须为数字');

        if( $amount <= 0 )
            throw new LogicException('充值金额必须大于0');

        if( $amount >= 10000000)
            throw new LogicException('请勿充值过大的金额');

        if(  ( (int)($amount*100) ) != ($amount * 100)  )
            throw new LogicException('充值金额的最小单位不得小于分');

    }
}


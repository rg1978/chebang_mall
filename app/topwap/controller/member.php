<?php
class topwap_ctl_member extends topwap_controller{

    public $limit = 10;
    public function __construct(&$app)
    {
        parent::__construct();
        kernel::single('base_session')->start();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        // 检测是否登录
        if( !userAuth::check() )
        {
            if( request::ajax() )
            {
                $url = url::action('topwap_ctl_passport@goLogin');
                return $this->splash('error', $url, app::get('topwap')->_('请登录'), true);
            }
            redirect::action('topwap_ctl_passport@goLogin')->send();exit;
        }

        $this->passport = kernel::single('topwap_passport');
    }
    public $verifyArray = array('mobile','email');

    public function index()
    {
        $this->setLayoutFlag('member');
        $userId = userAuth::id();
        $pagedata['account'] = userAuth::getLoginName();

        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;
        $pagedata['nologin'] = userAuth::check() ? "true" : "false";

        //获取订单各种状态的数量
        $pagedata['nupay'] = app::get('topwap')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_PAY'));
        $pagedata['nudelivery'] = app::get('topwap')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_SELLER_SEND_GOODS'));
        $pagedata['nuconfirm'] = app::get('topwap')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_CONFIRM_GOODS'));
        $cancelData = app::get('topwap')->rpcCall('trade.cancel.list.get',['user_id'=>$userId,'fields'=>'tid']);
        $pagedata['canceled'] = $cancelData['total'];
        $pagedata['nurate'] = app::get('topwap')->rpcCall('trade.notrate.count',array('user_id'=>$userId));

        //预存款金额
        $pagedata['deposit'] = app::get('topwap')->rpcCall('user.deposit.getInfo',['user_id'=>$userId]);
        $depositConf = app::get('topwap')->rpcCall('payment.get.conf',['app_id'=>'deposit']);
        $pagedata['noDeposit'] = $depositConf['status'] == 'true' ? false : true;

        //优惠劵数量
        $pagedata['coupon'] = app::get('topwap')->rpcCall('user.coupon.list', ['user_id'=>$userId, 'is_valid'=>'1', 'page_size'=>1]);

        //会员签到
        $pagedata['isCheckin'] = app::get('sysconf')->getConf('open.checkin');
        $pagedata['isPoint'] = app::get('sysconf')->getConf('open.point');
        $pagedata['checkinPointNum'] = app::get('sysconf')->getConf('checkinPoint.num');
        $params =array(
            'user_id' => $userId,
            'checkin_date' => date('Y-m-d'),
        );
        $pagedata['checkin_status'] = app::get('topwap')->rpcCall('user.get.checkin.info',$params);

        return $this->page('topwap/member/index.html', $pagedata);
    }

    public function security()
    {
        $pagedata['title'] = app::get('topwap')->_('安全中心');
        // 查看当前会员是否设置了手机
        $userInfo = userAuth::getUserInfo();
        $pagedata['user'] = $userInfo;
        return $this->page('topwap/member/safe_center.html', $pagedata);
    }
    public function setting()
    {
        return $this->page('topwap/member/setting.html');
    }

    public function detail()
    {
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;
        return $this->page('topwap/member/detail.html',$pagedata);
    }

    public function goSetName()
    {
        $userInfo = userAuth::getUserInfo();
        $pagedata['name'] = $userInfo['name'];
        return $this->page('topwap/member/set/name.html',$pagedata);
    }

    public function goSetUsername()
    {
        $userInfo = userAuth::getUserInfo();
        $pagedata['username'] = $userInfo['username'];
        return $this->page('topwap/member/set/username.html',$pagedata);
    }

    public function goSetSex()
    {
        $userInfo = userAuth::getUserInfo();
        $pagedata['sex'] = $userInfo['sex'];
        return $this->page('topwap/member/set/sex.html',$pagedata);
    }

    public function goSetLoginAccount()
    {
        $userInfo = userAuth::getUserInfo();
        return $this->page('topwap/member/set/login_account.html',$pagedata);
    }

    public function goSetBirthday()
    {
        $userInfo = userAuth::getUserInfo();
        $pagedata['name'] = $userInfo['name'];
        return $this->page('topwap/member/set/birthday.html',$pagedata);
    }

    public function saveUserInfo()
    {
        $userId = userAuth::id();
        $postdata = utils::_filter_input(input::get('user'));
        if(!$this->_validator($postdata,$msg))
        {
            return $this->splash('error',null,$msg);
        }

        try
        {
            $data = array('user_id'=>$userId,'data'=>json_encode($postdata));
            app::get('topwap')->rpcCall('user.basics.update',$data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $url = url::action('topwap_ctl_member@detail');
        $msg = app::get('topwap')->_('修改成功');
        return $this->splash('success',$url,$msg,true);
    }

    private function _validator($postdata,&$msg)
    {
        try
        {
            switch(key($postdata)) {
            case "username":
                $rule = ['username'=>'required|max:20'];
                $message = ['username' => '用户姓名不能为空!|用户姓名过长,请输入20个英文或10个汉字!'];
                break;
            case "name":
                $rule = ['name'=>'required|min:4|max:20'];
                $message = ['name' =>'用户昵称不能为空!|用户昵称最少4个字符!|用户昵最多20个字符!'];
                break;
            }
            $validator = validator::make($postdata,$rule,$message);
            $validator->newFails();
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return false;
        }
        return ture;
    }

    /**
     * 信任登陆用户名密码设置
     */
    public function saveLoginAccount()
    {
        $username = input::get('username');

        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();
        if($userInfo['login_account']){
            $msg = app::get('topwap')->_('您已有用户名，不能再设置');
            return $this->splash('error',null,$msg,true);
        }



        $url = url::action('topwap_ctl_member@detail');
        try
        {
            $this->__checkAccount($username);
            $data = array(
                'user_name'   => $username,
                'user_id' => $userId,
            );
            app::get('topwap')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }


        return $this->splash('success',$url,app::get('topwap')->_('修改成功'),true);
    }

    /**
     *  会员签到
     */
    public function checkin(){

        $url = url::action('topwap_ctl_member@index');
        try
        {
            $params = array(
                'user_id' => userAuth::id(),
            );
            app::get('topwap')->rpcCall('user.add.checkin.log',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        return $this->splash('success',$url,app::get('topc')->_('签到成功'),true);
    }

    private function __checkAccount($username)
    {

        $validator = validator::make(
            ['username' => $username],
            ['username' => 'loginaccount|required|min:4|max:20'],
            ['username' => '用户名不能为纯数字或邮箱地址!|用户名不能为空!|用户名最少4个字符！|用户名最长为20个字符!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new LogicException( $error[0] );
            }
        }
        return true;
    }
}

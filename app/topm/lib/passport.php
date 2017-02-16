<?php
class topm_passport{

    //前端注册验证码的发送
    public function sendVcode($account,$sendType)
    {
        if(!$account)
        {
            throw new \LogicException(app::get('topm')->_('请填写正确的手机号码或者邮箱号'));
        }

        if(!$sendType)
        {
            throw new \LogicException(app::get('topm')->_('参数错误'));
        }

        //$type = kernel::single('pam_tools')->checkLoginNameType($account);
        $type = app::get('topm')->rpcCall('user.get.account.type',array('user_name'=>$account),'buyer');
        if($type=='mobile')
        {
            $noticeType = '手机号码';
        }
        if($type=='email')
        {
            $noticeType = '邮箱';
        }

        $data = userAuth::getAccountInfo($account);
        $userId = userAuth::id();
        if($sendType=='reset'&&$type=='email')
        {
            if($data&&$data['user_id']!=$userId)
            {
                throw new \LogicException('该'.$noticeType.'已被使用');
            }
        }
        switch($type)
        {
        case "email":
            if( !$this->sendEmailVcode($sendType,$account))
            {
                throw new \LogicException(app::get('topm')->_('邮件发送失败'));
            }
            break;
        case "mobile":
            if( !userVcode::send_sms($sendType,$account) )
            {
                throw new \LogicException(app::get('topm')->_('验证码发送失败'));
            }
            break;
        default:
            throw new \LogicException(app::get('topm')->_('数据格式错误!'));
            break;
        }
        return true;
    }


    public function sendEmailVcode($sendType,$account)
    {
        $userId = userAuth::id();
        $resetUrl = url::action("topm_ctl_member@bindEmail",array('uname'=>$account,'type'=>$sendType,'verify'=>md5($userId)));
        $unresetUrl = url::action("topm_ctl_member@unVerifyEmail",array('uname'=>$account,'type'=>$sendType,'verify'=>md5($userId)));
        switch($sendType)
        {
        case 'activation':
        case 'reset':
            $content = url::action("topm_ctl_member@bindEmail",array('uname'=>$account,'type'=>$sendType,'verify'=>md5($userId),'next_page'=>$resetUrl));
            break;
        case 'unreset':
            $content = url::action("topm_ctl_member@unVerifyEmail",array('uname'=>$account,'type'=>$sendType,'verify'=>md5($userId),'next_page'=>$unresetUrl));
        break;
        case 'forgot':
        case 'signup': //手机注册
            $content = url::action("topm_ctl_passport@findPwdThree",array('uname'=>$account,'type'=>$sendType));
            break;
        case 'depost_forgot'://预存款忘记密码
            $content = url::action("topm_ctl_member_deposit@forgetPasswordSetPassword",array('uname'=>$account,'type'=>$sendType));
            break;
        }

        if(!userVcode::send_email($sendType,$account,$content))
        {
            return false;
        }
        return true;
    }
}

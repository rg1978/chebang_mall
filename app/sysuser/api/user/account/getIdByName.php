<?php

class sysuser_api_user_account_getIdByName {

    public $apiDescription = "根据用户名/手机/邮箱 获取会员ID";

    public function getParams()
    {
        $return['params'] = array(
            'user_name' => ['type'=>'string','valid'=>'required', 'description'=>'会员用户名/手机号/邮箱','default'=>'','example'=>''],
        );
        return $return;
    }
    public function getId($params)
    {
        if( $params['user_name'] )
        {
            $userName = explode(',',$params['user_name']);
            foreach( $userName as $account )
            {
                $type = kernel::single('pam_tools')->checkLoginNameType($account);
                $filter[$type][] = $account;
            }
        }

        $result = array();
        if( $filter['login_account'] )
        {
            $aData = app::get('sysuser')->model('account')->getList('user_id,login_account',['login_account'=>$filter['login_account']]);
            foreach( $aData as $row )
            {
                $result[$row['login_account']] = $row['user_id'];
            }
        }

        if( $filter['mobile'] )
        {
            $aData = app::get('sysuser')->model('account')->getList('user_id,mobile',['mobile'=>$filter['mobile']]);
            foreach( $aData as $row )
            {
                $result[$row['mobile']] = $row['user_id'];
            }
        }

        if( $filter['email'] )
        {
            $aData = app::get('sysuser')->model('account')->getList('user_id,email',['email'=>$filter['email']]);
            foreach( $aData as $row )
            {
                $result[$row['mobile']] = $row['user_id'];
            }
        }

        return $result;
    }
}


<?php
class sysuser_mdl_user_deposit_cash extends dbeav_model
{
    var $defaultOrder = array('create_time','DESC');

    function _filter($filter = array()){
        if(isset($filter['user_id']) && !is_numeric($filter['user_id']) && is_string($filter['user_id']))
        {
            $userMdl = app::get('sysuser')->model('account');
            $userId = $userMdl->getRow('user_id', ['login_account|has'=>$filter['user_id']]);
            if(!empty($userId['user_id']))
                $filter['user_id'] = $userId['user_id'];
        }
        return parent::_filter($filter);

    }
}

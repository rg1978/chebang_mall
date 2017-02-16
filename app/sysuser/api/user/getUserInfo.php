<?php
class sysuser_api_user_getUserInfo{
    public $apiDescription = "获取用户的详细信息";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'fields' => ['type'=>'field_list','valid'=>'', 'description'=>'查询字段','default'=>'','example'=>''],
        );
        return $return;
    }

    public function getList($params)
    {
        $userId = $params['user_id'];
        if(!$params['user_id'])
        {
            $userId = $params['oauth']['account_id'];
        }

        $userData = kernel::single('sysuser_passport')->memInfo($userId);
        return $userData;
    }
}


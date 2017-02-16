<?php
class sysuser_api_user_deposit_cashGetConf
{
    public $apiDescription = "获取配置信息";

    public function getParams()
    {
        $return['params'] = array(
            'user_id'         => ['type'=>'int',       'valid'=>'numeric', 'title'=>'用户id',     'desc'=>'用户id'],
        );
        return $return;
    }

    public function get($params)
    {
        $userId = $params['user_id'] ? $params['user_id'] : 0;
        return kernel::single('sysuser_data_deposit_cash')->getConf($userId);
    }
}


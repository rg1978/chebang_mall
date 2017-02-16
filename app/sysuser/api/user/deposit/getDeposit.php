<?php
class sysuser_api_user_deposit_getDeposit{
    public $apiDescription = "获取用户的预存款信息";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'with_log' => ['type'=>'bool','valid'=>'', 'description'=>'是否查询日志','default'=>'','example'=>''],
            'page' => ['type'=>'bool','valid'=>'numeric|min:0', 'description'=>'日志列表的页数','default'=>'1','example'=>''],
            'row_num' => ['type'=>'bool','valid'=>'numeric|min:0', 'description'=>'日志列表每页的行数','default'=>'10','example'=>''],
        );
        return $return;
    }

    public function getInfo($params)
    {
        $userId = $params['user_id'];
        if(!$params['user_id'])
        {
            $userId = $params['oauth']['account_id'];
        }

        $deposit['deposit'] = kernel::single('sysuser_data_deposit_deposit')->get($userId);
        if($params['with_log'])
        {
            $page = $params['page'] ? $params['page'] : 1;
            $rowNum = $params['row_num'] ? $params['row_num'] : 10;
            list($deposit['list'], $deposit['count']) = kernel::single('sysuser_data_deposit_log')->get($userId, $page, $rowNum);
        }

        return $deposit;
    }
}


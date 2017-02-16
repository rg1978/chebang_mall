<?php
class sysuser_api_point_update{

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新会员的积分值';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员ID'],
            'type' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'积分记录类型("获得","消费")'],
            'num' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'积分数量'],
            'behavior' =>['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'积分行为'],
            'remark' =>['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'备注'],
        );

        return $return;
    }

    public function updateUserPoint($params)
    {
        switch($params['type'])
        {
        case "obtain":
            $params['modify_point'] = abs($params['num']);
            break;
        case "consume":
            $params['modify_point'] = 0-$params['num'];
            break;
        }

        $paramsPoint = array(
            'user_id' => $params['user_id'],
            'modify_remark' => $params['remark'],
            'modify_point' => $params['modify_point'],
            'behavior' => $params['behavior'],
        );

        $objDataPoints = kernel::single('sysuser_data_user_points');
        $result = $objDataPoints->changePoint($paramsPoint);
        return $result;

    }
}

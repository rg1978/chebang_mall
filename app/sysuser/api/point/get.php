<?php
class sysuser_api_point_get{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取指定会员的积分值';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员ID'],
        );

        return $return;
    }

    public function get($params)
    {
        $objMdlUserPoint = app::get('sysuser')->model('user_points');
        $points = $objMdlUserPoint->getRow('*',$params);
        return $points;
    }

}

<?php
class sysuser_api_point_setting{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取积分的配置信息';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'field' => ['type'=>'', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'积分设置key值'],
        );
        return $return;
    }

    public function get($params)
    {
        $params = $params['field'];
        $point_ratio = app::get('sysconf')->getConf('point.ratio');
        $point_expired_month = app::get('sysconf')->getConf('point.expired.month');
        $open_point_deduction = app::get('sysconf')->getConf('open.point.deduction');
        $point_deduction_rate = app::get('sysconf')->getConf('point.deduction.rate');
        $point_deduction_max = app::get('sysconf')->getConf('point.deduction.max');
        $data = array(
            'point.ratio' => $point_ratio ? $point_ratio : 1,
            'point.expired.month' => $point_expired_month ? $point_expired_month : 12,
            'open.point.deduction' => $open_point_deduction ? $open_point_deduction : 0,
            'point.deduction.rate' => $point_deduction_rate ? $point_deduction_rate : 100,
            'point.deduction.max' => $point_deduction_max ? ($point_deduction_max/100) : 0.99,
        );
        if($params)
        {
            return $data[$params];
        }
        return $data;
    }


}

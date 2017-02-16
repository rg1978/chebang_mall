<?php
class sysuser_api_point_computeDeduction{
    /**
     * 接口作用说明
     */
    public $apiDescription = '根据订单总金额平摊使用的积分';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员ID'],
            'use_point' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员使用的积分'],
            'total_money' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'本次订单总额'],
            'trade_money' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺订单总额'],
        );

        return $return;
    }

    public function compute($params)
    {
        $usePoint = $params['use_point'];
        $total_money = $params['total_money'];
        $trade_money = $params['trade_money'];

        //是否开启了积分抵扣
        $open_point_deduction = app::get('sysconf')->getConf('open.point.deduction');
        $open_point_deduction = $open_point_deduction ? $open_point_deduction : 0;

        if(!$open_point_deduction)
        {
            return array();
        }
        //积分抵扣比率
        $point_deduction_rate = app::get('sysconf')->getConf('point.deduction.rate');
        $point_deduction_rate = $point_deduction_rate ? $point_deduction_rate : 100;

        //积分抵扣上限
        $point_deduction_max = app::get('sysconf')->getConf('point.deduction.max');
        $point_deduction_max = $point_deduction_max ? ($point_deduction_max/100) : 0.99;

        //计算可抵扣的最大积分值
        $maxPoints = floor($point_deduction_max*$total_money*$point_deduction_rate);
        
        if(intval($usePoint) > intval($maxPoints) )
        {
            throw new \LogicException('积分使用超出上限'.$point_deduction_max*$total_money);
        }

        //会员现有的积分值
        $objMdlUserPoint = app::get('sysuser')->model('user_points');
        $points = $objMdlUserPoint->getRow('point_count',array('user_id'=>$params));
        $userPoints = $points['point_count'];

        $proportion_money = ecmath::number_div(array($usePoint, $point_deduction_rate) );
        $min = ecmath::number_div(array(1, $point_deduction_rate) );
        if($proportion_money <= $min )
        {
            return array();
        }
        //将订单金额和使用积分同比增加100倍
        $total_money = intval(ecmath::number_multiple(array($total_money, 100)));
        $usePoint = intval(ecmath::number_multiple(array($usePoint, 100)));

        //计算积分和总金额的占比(此处不能进行小数位取舍)
        $percent = $usePoint/$total_money;

        //计算订单抵扣值
        $proportion_point = ecmath::number_multiple(array($trade_money, $percent));
        $proportion_point = ecmath::formatNumber($proportion_point,0,2);

        //计算积分抵扣的金额
        $proportion_money = ecmath::number_div(array($proportion_point, $point_deduction_rate) );

        $data = array(
            'point' => $proportion_point,
            'money' => $proportion_money,
        );

        return $data;
    }
}

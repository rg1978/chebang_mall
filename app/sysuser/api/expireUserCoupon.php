<?php
class sysuser_api_expireUserCoupon{

    /**
     * 接口作用说明
     */
    public $apiDescription = '过期优惠券状态更改';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'coupon_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'优惠券ID必填'],
        );

        return $return;
    }

    public function updateCoupon($params)
    {
        $filter['coupon_id'] = explode(',',$params['coupon_id']);
        return app::get('sysuser')->model('user_coupon')->update(array('is_valid'=>'2'), $filter);
    }


}

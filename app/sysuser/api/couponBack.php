<?php
class sysuser_api_couponBack {

    /**
     * 接口作用说明
     */
    public $apiDescription = '取消订单返还优惠券';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'tid' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'主订单号','default'=>'','example'=>''],
        );

        return $return;
    }

    public function couponBack($apiData)
    {
        $filter['tid'] = $apiData['tid'];
        $stmt = app::get('sysuser')->model('user_coupon')->update(array('is_valid'=>'1'), $filter);
        if( $stmt > 0 )
        {
            $this->updateCouponQuantity($apiData);
        }

        return $stmt;
    }

    public function updateCouponQuantity($apiData)
    {
        $filter['tid'] = $apiData['tid'];
        $filter['user_id'] = pamAccount::getAccountId();
        $objMdlUserCoupon = app::get('sysuser')->model('user_coupon');
        $coupondata = $objMdlUserCoupon->getList('coupon_id,shop_id,is_valid',$filter);
        if( empty($coupondata) ) return true;
        $params =array(
            'user_id' => $filter['user_id'],
            'shop_id' => $coupondata[0]['shop_id'],
            'coupon_id' => $coupondata[0]['coupon_id'],
            'is_valid' => $coupondata[0]['is_valid'],
        );

        return app::get('sysuser')->rpcCall('promotion.coupon.updateCouponQuantity',$params);
    }
}

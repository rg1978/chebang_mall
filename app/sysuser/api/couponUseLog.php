<?php
class sysuser_api_couponUseLog {

    /**
     * 接口作用说明
     */
    public $apiDescription = '修改优惠券使用信息';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'coupon_code' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'优惠券编码','default'=>'','example'=>''],
            'tid' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'订单id','default'=>'','example'=>''],
        );

        return $return;
    }

    public function couponUseLog($apiData)
    {
        $data['tid'] = $apiData['tid'];
        $data['is_valid'] = '0';
        $filter['user_id'] = pamAccount::getAccountId();
        $filter['coupon_code'] = $apiData['coupon_code'];
        $flag = app::get('sysuser')->model('user_coupon')->update($data, $filter);
        if ($flag) {
            $this->updateCouponQuantity($apiData);
        }

	return $flag;

    }

    public function updateCouponQuantity($apiData)
    {
        $filter['tid'] = $apiData['tid'];
        $filter['user_id'] = pamAccount::getAccountId();
        $filter['coupon_code'] = $apiData['coupon_code'];
        $objMdlUserCoupon = app::get('sysuser')->model('user_coupon');
        $coupondata = $objMdlUserCoupon->getList('coupon_id,shop_id,is_valid',$filter);
        $params =array(
            'user_id' => $filter['user_id'],
            'shop_id' => $coupondata[0]['shop_id'],
            'coupon_id' => $coupondata[0]['coupon_id'],
            'is_valid' => $coupondata[0]['is_valid'],
        );
        app::get('sysuser')->rpcCall('promotion.coupon.updateCouponQuantity',$params);
    }
}

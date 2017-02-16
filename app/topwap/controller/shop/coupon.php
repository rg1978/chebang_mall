<?php
class topwap_ctl_shop_coupon extends topwap_controller{

    public function index()
    {
        $shopId = input::get('shop_id');
        // 店铺优惠券信息,
        $params = array(
            'page_no' => 0,
            'page_size' => 10,
            'fields' => '*',
            'shop_id' => $shopId,
            'platform' => 'wap',
            'is_cansend' => 1,
        );
        $couponListData = app::get('topwap')->rpcCall('promotion.coupon.list', $params, 'buyer');
        $pagedata['shopCouponList'] = $couponListData['coupons'];

        return $this->page('topwap/shop/coupon/list.html', $pagedata);
    }

    public function receiveConpon()
    {
        //print_r(input::get());exit;
        $shopId = input::get('shop_id');
        $couponId = input::get('coupon_id');
        $userId = userAuth::id();
        if(!$userId)
        {
            $loginUrl = url::action('topwap_ctl_passport@goLogin');
            return $this->splash('error', $loginUrl, '请登录', true);
        }
        $validator = validator::make(
            [$couponId],
            ['required|numeric']
        );
        if ($validator->fails())
        {
            return $this->splash('error',null,'领取优惠券参数错误!',true);
        }

        try
        {
            $userInfo = app::get('topwap')->rpcCall('user.get.info',array('user_id'=>$userId),'buyer');
            $apiData = array(
                 'coupon_id' => $couponId,
                 'user_id' =>$userId,
                 'shop_id' =>$shopId,
                 'grade_id' =>$userInfo['grade_id'],
            );
            if(app::get('topwap')->rpcCall('user.coupon.getCode', $apiData))
            {
                return $this->splash('success', null, '优惠券领取成功', true);
            }
            else
            {
                return $this->splash('error', null, '领取失败', true);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }
    }
}

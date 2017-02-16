<?php
/**
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topapi_cart_checkout {

    public function total($postData)
    {
        if($postData['current_shop_id'])
        {
            $current_shop_id = $postData['current_shop_id'];
            unset($postData['current_shop_id']);
        }

        $addrId = $postData['addr_id'];
        if($addrId)
        {
            $params['user_id'] = $postData['user_id'];
            $params['addr_id'] = $addrId;
            $params['fields'] = 'area';
            $addr = app::get('topwap')->rpcCall('user.address.info',$params,'buyer');
            list($regions,$region_id) = explode(':', $addr['area']);
        }

        $cartFilter['mode'] = $postData['mode'] ? $postData['mode'] :'cart';
        $cartFilter['needInvalid'] = false;
        $cartFilter['platform'] = 'wap';
        $cartFilter['user_id'] = $postData['user_id'];
        $cartInfo = app::get('topwap')->rpcCall('trade.cart.getCartInfo', $cartFilter,'buyer');

        $allPayment = 0;
        $objMath = kernel::single('ectools_math');
        foreach ($cartInfo['resultCartData'] as $shop_id => $tval) {
            $shippingType = $postData['shipping'][$tval['shop_id']]['shipping_type'];
            $shippingType = $shippingType ?: 'express';
            $totalParams = array(
                'discount_fee' => $tval['cartCount']['total_discount'],
                'total_fee' => $tval['cartCount']['total_fee'],
                'total_weight' => $tval['cartCount']['total_weight'],
                'shop_id' => $tval['shop_id'],
                'shipping_type' => $shippingType,
                'region_id' => $region_id ? str_replace('/', ',', $region_id) : '0',
                'usedCartPromotionWeight' => $tval['usedCartPromotionWeight'],
                'usedToPostage' => json_encode($tval['cartByDlytmpl']),
            );
            $totalInfo = app::get('topwap')->rpcCall('trade.price.total',$totalParams,'buyer');
            $trade_data['allPayment'] = $objMath->number_plus(array($trade_data['allPayment'] ,$totalInfo['payment']));
            $trade_data['allPostfee'] = $objMath->number_plus(array($trade_data['allPostfee'] ,$totalInfo['post_fee']));
            $trade_data['disCountfee'] = $objMath->number_plus(array($trade_data['disCountfee'] ,$totalInfo['discount_fee']));
            if($current_shop_id && $shop_id != $current_shop_id)
            {
                continue;
            }

            $trade_data['shop'][$shop_id]['payment'] = $totalInfo['payment'];
            $trade_data['shop'][$shop_id]['total_fee'] = $totalInfo['total_fee'];
            $trade_data['shop'][$shop_id]['discount_fee'] = $totalInfo['discount_fee'];
            $trade_data['shop'][$shop_id]['obtain_point_fee'] = $totalInfo['obtain_point_fee'];
            $trade_data['shop'][$shop_id]['post_fee'] = $totalInfo['post_fee'];
            $trade_data['shop'][$shop_id]['totalWeight'] += $tval['cartCount']['total_weight'];
        }
        return $trade_data;
    }

    public function totalWithPoint($params)
    {
        $total = $this->total($params);
        $user_id = $params['user_id'];
        $total_price = $total['allPayment'];
        $post_fee = $total['allPostfee'];

        return [
            'total'=>$total,
            'userPoint'=>$this->userPoint($user_id, $total_price, $post_fee),
        ];
    }

    public function userPoint($user_id, $total_price, $post_fee)
    {
        $totalPrice = $total_price;
        $totalPostFee = $post_fee;
        $totalPrice = $totalPrice-$totalPostFee;
        $userId = $user_id;
        //根据会员id获取积分总值
        $points = app::get('topwap')->rpcCall('user.point.get',['user_id'=>$userId]);
        $setting = app::get('topwap')->rpcCall('point.setting.get');
        $pagedata['open_point_deduction'] = $setting['open.point.deduction'];
        $pagedata['point_deduction_rate'] = $setting['point.deduction.rate'];
        $pagedata['point_deduction_max'] = floor($setting['point.deduction.max']*$totalPrice*$setting['point.deduction.rate']);
        $pagedata['points'] = $points['point_count'] ? $points['point_count'] : 0;

        return $pagedata;

    }

}



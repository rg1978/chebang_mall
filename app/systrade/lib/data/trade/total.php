<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class systrade_data_trade_total {

    /**
     * 生成订单总计详细
     * @params object 控制器
     * @params object cart objects
     * @params array sdf array
     */
    public function trade_total_method($params)
    {
        $total_fee = $params['total_fee'];
        $items_weight = $params['total_weight'];
        $shipping_type = $params['shipping_type'];
        $shopId = $params['shop_id'];
        $region_id = $params['region_id'];
        $usedCartPromotionWeight = $params['usedCartPromotionWeight'];
        $discount_fee = $params['discount_fee'];
        $usedToPostage = json_decode($params['usedToPostage'],1);

        // 运费计算
        $post_fee = 0;
        if( $region_id && $shipping_type=='express')
        {
            foreach($usedToPostage as $k=>$v)
            {
                $params = array(
                    'areaIds' => $region_id,
                    'template_id' => $k,
                    'total_price' => $v['total_price'],
                    'total_quantity' => $v['total_quantity'],
                    'total_weight' => $v['total_weight'],
                );
                $postFees[$k] = app::get('systrade')->rpcCall('logistics.fare.count',$params);
            }
            $post_fee = ecmath::number_plus($postFees);

            if($post_fee<0)
            {
                $post_fee = 0;
            }
        }
        elseif($shipping_type=='ziti')
        {
            $post_fee = 0;
        }
        else
        {
            throw new \LogicException(app::get('systrade')->_('请选择正确的配送类型！'));
        }

        $payment = ecmath::number_plus(array($total_fee, $post_fee));
        $payment = ecmath::number_minus(array($payment, $discount_fee));
        if($payment < 0)
        {
            $payment = 0.01; //不可以有0元订单，最小值为0.01；后续改造
        }

        //计算商品总额所获积分
        $totalFee = $payment-$post_fee;
        $subtotal_obtain_point = app::get('systrade')->rpcCall('user.pointcount',array('money'=>$totalFee));

        $payment_detail = array(
            'total_fee'=>$total_fee,
            'post_fee'=>$post_fee,
            'payment'=>$payment,
            'discount_fee' => $discount_fee,
            'obtain_point_fee' => $subtotal_obtain_point,
        );

        return $payment_detail;
    }
}



<?php
/**
 * ShopEx licence
 * - user.coupon.count
 * - 用于获取用户优惠券数量
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-27
 */
class sysuser_api_couponCount {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取用户优惠券数量';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id'   => ['type'=>'int',        'valid'=>'required', 'title'=>'用户ID',    'example'=>'', 'desc'=>'用户ID必填'],
            'shop_id'   => ['type'=>'int',        'valid'=>'',         'title'=>'店铺ID',    'example'=>'', 'desc'=>'店铺ID'],
            'is_valid'  => ['type'=>'int',        'valid'=>'',         'title'=>'有效优惠券', 'example'=>'', 'desc'=>'获取是否有效的参数'],
            'platform'  => ['type'=>'string',     'valid'=>'',         'title'=>'使用平台',   'example'=>'', 'desc'=>'优惠券使用平台'],
        );

        return $return;
    }

    /**
     * 用于获取用户优惠券数量
     * @desc 用于用于获取用户优惠券数量
     * @return int count 优惠券查询数量
     */
    public function couponCount($params)
    {
        $objMdlUserCoupon = app::get('sysuser')->model('user_coupon');

        if($params['shop_id'])
        {
            $filter = array('user_id'=>$params['user_id'], 'shop_id'=>$params['shop_id']);
        }
        else
        {
            $filter = array('user_id'=>$params['user_id']);
        }

        if( $params['platform'] == 'pc' )
        {
            $filter['used_platform'] = array('0', '1');
        }
        elseif( $params['platform'] == 'wap' )
        {
            $filter['used_platform'] = array('0', '2');
        }

        if(isset($params['is_valid']))
        {
            $filter['is_valid'] = $params['is_valid'];
        }


        $count = $objMdlUserCoupon->count($filter);
        $data = ['count' => $count];

        return $data;
    }

}

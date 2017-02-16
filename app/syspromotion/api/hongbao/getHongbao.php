<?php
/**
 * ShopEx licence
 * - promotion.hongbao.issued
 * - 发放红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class syspromotion_api_hongbao_getHongbao {

    /**
     * 接口作用说明
     */
    public $apiDescription = '发放红包接口';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'    => ['type'=>'string', 'valid'=>'required',  'title'=>'用户ID',       'desc'=>'用户ID'],
            'hongbao_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'红包ID',       'desc'=>'红包ID'],
            'money'      => ['type'=>'string', 'valid'=>'required',  'title'=>'获取指定红包', 'desc'=>'获取指定红包'],
            'hongbao_obtain_type'=> ['type'=>'string', 'valid'=>'required',  'title'=>'获取红包方式', 'desc'=>'获取红包方式 aftersales售后退红包，cancelTrade取消订单退红包 userGet用户主动获取红包'],
        );
        return $return;
    }

    /**
     * 发放红包接口
     *
     * @desc 发放红包接口
     * @return int hongbao_id 红包ID
     * @return int hongbao_type 红包类型
     * @return time use_start_time 红包使用开始时间
     * @return time use_end_time 红包使用结束时间
     * @return string used_platform 红包可使用平台
     * @return int money 发放红包的金额
     */
    public function get($params)
    {
        return kernel::single('syspromotion_hongbao')->getHongbao($params['user_id'], $params['hongbao_id'], $params['money'], $params['hongbao_obtain_type']);
    }
}


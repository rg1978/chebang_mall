<?php
/**
 * ShopEx licence
 * - promotion.hongbao.batch.issued
 * - 发放红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class syspromotion_api_hongbao_batchGetHongbao {

    /**
     * 接口作用说明
     */
    public $apiDescription = '批量发放红包接口';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'    => ['type'=>'string', 'valid'=>'required',  'title'=>'用户ID',       'desc'=>'用户ID'],
            'hongbao_list' => ['type'=>'string', 'valid'=>'required',  'title'=>'获取红包list',       'desc'=>'获取红包list'],
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
        $hongbaoList = json_decode($params['hongbao_list'], true);
        return kernel::single('syspromotion_hongbao')->batchGetHongbao($params['user_id'], $hongbaoList, $params['hongbao_obtain_type']);
    }
}


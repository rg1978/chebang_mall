<?php
/**
 * ShopEx licence
 * - user.hongbao.count
 * - 统计用户红包总金额
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class sysuser_api_user_hongbao_count {

    /**
     * 接口作用说明
     */
    public $apiDescription = '统计用户红包总金额';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string', 'valid'=>'required',  'desc'=>'用户ID'],
        );
        return $return;
    }

    /**
     * 统计用户红包总金额
     *
     * @desc 统计用户红包总金额
     * @return string hongbao_total
     */
    public function get($params)
    {
        $filter['is_valid'] = 'active';
        $filter['end_time|than'] = time();
        $filter['user_id'] = $params['user_id'];

        $return['hongbao_total'] = 0;
        $objMdlUserHongbao = app::get("sysuser")->model('user_hongbao');
        $data =  $objMdlUserHongbao->getList('money', $filter);
        if( empty($data) ) return $return;

        foreach( $data as $row )
        {
            $return['hongbao_total'] = ecmath::number_plus(array($return['hongbao_total'], $row['money']));
        }

        return $return;
    }
}


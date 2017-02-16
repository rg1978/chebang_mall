<?php
/**
 * ShopEx licence
 *
 * - promotion.hongbao.get
 * - 获取单个红包详情
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 *
 */
final class syspromotion_api_hongbao_info {

    public $apiDescription = '获取单个红包详情';

    public function getParams()
    {
        $return['params'] = array(
            'hongbao_id'     => ['type'=>'int',         'valid'=>'required', 'title'=>'红包ID',     'description'=>'红包ID'],
            'fields'         => ['type'=>'field_list',  'valid'=>'required', 'title'=>'需要的字段', 'description'=>'需要的字段'],
        );
        return $return;
    }

    /**
     * 获取单个红包详情
     */
    public function get($params)
    {
        $result = kernel::single('syspromotion_hongbao')->getHongbaoInfo($params['hongbao_id'], $params['fields']);
        return $result;
    }
}


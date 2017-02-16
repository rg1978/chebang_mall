<?php
/**
 * ShopEx licence
 * - promotion.hongbao.use
 * - 使用红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class syspromotion_api_hongbao_useHongbao {

    /**
     * 接口作用说明
     */
    public $apiDescription = '使用红包接口';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'          => ['type'=>'string', 'valid'=>'required',  'title'=>'用户ID',       'desc'=>'用户ID'],
            'use_hongbao_list' => ['type'=>'string', 'valid'=>'required',  'title'=>'使用红包列表', 'desc'=>'使用红包列表 {"hongbao_id":"money","红包ID":"该红包规则使用的总金额"}'],
        );
        return $return;
    }

    /**
     * 使用红包接口
     *
     * @desc 使用红包接口
     * @return bool result 使用是否成功
     */
    public function useHongbao($params)
    {
        $useHongbaoList = json_decode($params['use_hongbao_list'], true);
        return kernel::single('syspromotion_hongbao')->useHongbao($params['user_id'], $useHongbaoList);
    }
}


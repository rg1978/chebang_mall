<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 组合促销促销规则应用
 * promotion.package.apply
 */
final class syspromotion_api_package_packageApply {

    public $apiDescription = '组合促销促销规则应用';

    public function getParams()
    {
        $return['params'] = array(
            'package_id' => ['type'=>'int', 'valid'=>'required|integer', 'default'=>'', 'example'=>'', 'description'=>'组合促销促销表id'],
            'promotion_id' => ['type'=>'int', 'valid'=>'required|integer', 'default'=>'', 'example'=>'', 'description'=>'促销关联表id'],
            'forPromotionTotalPrice' => ['type'=>'float', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'符合应用促销的商品总价'],
        );

        return $return;
    }

    /**
     *  组合促销促销规则应用
     * @param  array $params 筛选条件数组
     * @return array         返回一条促销详情
     */
    public function packageApply($params)
    {
        $data = array(
            'user_id' => $params['oauth']['account_id'],
            'package_id'=>$params['package_id'],
            'promotion_id' => $params['promotion_id'],
            'forPromotionTotalPrice' => $params['forPromotionTotalPrice'],
        );
        $discount_price = kernel::single('syspromotion_solutions_package')->apply($data);

        return $discount_price;
    }


}


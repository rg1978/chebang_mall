<?php
/**
 * getCurrency.php 获取当前平台设置的货币符号
 * Created Time 2016年3月4日 下午4:38:09
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class ectools_api_getCurrency{

    public $apiDescription = "获取当前平台设置的货币和精度";

    public function getParams()
    {
        $return['params'] = array(
            'cur' => ['type'=>'string','valid'=>'','description'=>'指定货币的简称','default'=>'CNY','example'=>'CNY'],
        );
        return $return;
    }

    public function getCur($params)
    {
        if(isset($params['cur']))
        {
            $cur = $params['cur'];
        }
        else
        {
            $cur = app::get('ectools')->getConf('system.currency.default');
        }

        $objCurrency = kernel::single('ectools_data_currency');
        $rs['sign'] = $objCurrency->getCurrency('sign', $cur);
        $rs['decimals'] = app::get('ectools')->getConf('system.money.decimals');

        return $rs;
    }
}

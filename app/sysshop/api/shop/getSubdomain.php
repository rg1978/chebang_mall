<?php

/**
 * ShopEx licence
 * - shop.subdomain.get
 * - 用于获取二级域名信息
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-08-09
 */
class sysshop_api_shop_getSubdomain{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = "获取二级域名信息";

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int','valid'=>'required|min:1','desc'=>'店铺id','default'=>'','example'=>'1'],
        );
        return $return;
    }

    /**
     * 申请域名信息
     * @desc 用于申请域名信息
     * @return shop_id int 店铺id
     * @return subdomain string 二级域名,如果店铺没有申请，则默认规则是字符串'shop'和店铺id的连接字符串,如'shop5';
     * @return seller_id int 商家账号id，一般是店主
     * @return times int 域名修改次数
     * @return modified_time timestamp 最后修改时间时间戳
     * @return subdomain_enabled bool 二级域名申请开启状态
     * @return subdomain_limits timestamp 二级域名最大修改次数
     */
    public function getSubdomain($params)
    {
        $objMdlSubdomain = app::get('sysshop')->model('subdomain');
        $filter = array(
            'shop_id' => $params['shop_id'],
        );
        $data = $objMdlSubdomain->getRow('*', $filter);
        // 如果店铺没有填写二级域名，则生成默认的二级域名
        if(!$data['subdomain'])
        {
            $data['shop_id'] = $params['shop_id'];
            $data['subdomain'] = 'shop'.$params['shop_id'];
        }
        $data['subdomain_limits'] = app::get('site')->getConf('site.subdomain_limits');
        $data['subdomain_enabled'] = app::get('site')->getConf('site.subdomain_enabled');
        $data['subdomain_basic'] = app::get('site')->getConf('site.subdomain_basic');
        return $data;
    }
}

<?php

/**
 * ShopEx licence
 * - shop.subdomain.getshopid
 * - 根据二级域名获取商家id
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-08-09
 */
class sysshop_api_shop_getShopIdBySubdomain{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = "根据二级域名获取商家id";

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'subdomain' => ['type'=>'string','valid'=>'required','desc'=>'店铺子域名','default'=>'','example'=>'shop5'],
        );
        return $return;
    }

    /**
     * 用于获取店铺id
     * @desc 用于获取店铺id
     * @return shop_id int 店铺id
     */
    public function getShopId($params)
    {
        $objMdlSubdomain = app::get('sysshop')->model('subdomain');
        $filter = array(
            'subdomain' => $params['subdomain'],
        );
        $data = $objMdlSubdomain->getRow('shop_id', $filter);
        // 如果店铺没有填写二级域名，则生成默认的二级域名
        if(!$data['shop_id'])
        {
            $defaultstr = substr($params['subdomain'], 0, 4);
            if($defaultstr!='shop')
            {
                throw new \LogicException('店铺二级域名找不到！');
            }
            $shopid = substr($params['subdomain'], 4);
            if(!preg_match("/^\+?[1-9][0-9]*$/", $shopid))
            {
                throw new \LogicException('店铺二级域名找不到！');
            }
            $data['shop_id'] = intval($shopid);
        }

        return $data;
    }
}

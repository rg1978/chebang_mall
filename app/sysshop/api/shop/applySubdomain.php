<?php

/**
 * ShopEx licence
 * - shop.subdomain.apply
 * - 用于申请二级域名信息
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-08-09
 */
class sysshop_api_shop_applySubdomain{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = "申请二级域名信息";

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'shop_id'   => ['type'=>'int',    'valid'=>'required',              'title'=>'店铺id',    'example'=>'', 'desc'=>'店铺id'],
            'seller_id' => ['type'=>'int',    'valid'=>'required',              'title'=>'商户店主id', 'example'=>'', 'desc'=>'商户店主id'],
            'subdomain' => ['type'=>'string', 'valid'=>'required|min:4|max:32', 'title'=>'二级域名',   'example'=>'', 'desc'=>'二级域名'],
        );
        return $return;
    }


    /**
     * 申请二级域名信息
     * @desc 用于申请二级域名信息
     * @return bool [true/false] 返回执行状态
     */
    public function applySubdomain($params)
    {
        $subdomain_enabled = app::get('site')->getConf('site.subdomain_enabled');
        $subdomain_limits = app::get('site')->getConf('site.subdomain_limits');

        if(!$subdomain_enabled)
        {
            throw new \LogicException('没有开放二级域名申请！');
        }
        if(!preg_match('/^[\pL\pM\pN][\pL\pM\pN-]{2,30}[\pL\pM\pN]$/u', $params['subdomain']))
        {
            throw new \LogicException('二级域名长度必须为4-32个"字母、数字、-" 组成的字符串，且中杠不能在开头和结尾!');
        }
        $objMdlSubdomain = app::get('sysshop')->model('subdomain');
        $domainInfo = $objMdlSubdomain->getRow('*', [ 'shop_id' => $params['shop_id'] ]);
        if( $domainInfo['times'] >= $subdomain_limits )
        {
            throw new \LogicException('最多申请'.$subdomain_limits.'次！');
        }
        if( $domainInfo['subdomain'] == $params['subdomain'] )
        {
            throw new \LogicException('二级域名和上次相同！');
        }

        $samedomain = $objMdlSubdomain->getRow('shop_id', [ 'subdomain' => $params['subdomain'] ]);
        if($samedomain)
        {
            throw new \LogicException('此二级域名已被使用！');
        }
        $apidata = [
            'shop_id' => $params['shop_id'],
            'seller_id' => $params['seller_id'],
            'subdomain' => trim($params['subdomain']),
            'times' => $domainInfo['times']+1,
            'modified_time' => time(),
        ];
        $data = $objMdlSubdomain->save($apidata);

        return $data;
    }
}

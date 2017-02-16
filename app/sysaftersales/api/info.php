<?php
/**
 * ShopEx licence
 * - aftersales.get
 * - 获取单个售后详情
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取单条售后申请数据
 */
class sysaftersales_api_info {

     /**
     * 接口作用说明
     */
    public $apiDescription = '获取单个售后详情';

    public function getParams()
    {
        $return['params'] = array(
            'aftersales_bn' => ['type'=>'int',       'valid'=>'required|numeric', 'title'=>'',  'desc'=>'申请售后编号'],
            'shop_id'       => ['type'=>'int',       'valid'=>'int',              'title'=>'',  'desc'=>'售后单所属店铺的店铺id'],
            'user_id'       => ['type'=>'int',       'valid'=>'int',              'title'=>'',  'desc'=>'售后单所属用户的用户id'],
            'fields'        => ['type'=>'field_list','valid'=>'required',         'title'=>'',  'desc'=>'获取单条售后需要返回的字段'],
        );

        $return['extendsFields'] = ['trade','sku','attachment'];

        return $return;
    }

    /**
     * 获取单条申请售后服务信息
     */
    public function getData($params)
    {
        $filter['aftersales_bn'] = $params['aftersales_bn'];
        if($params['shop_id'])
        {
            $filter['shop_id'] = $params['shop_id'];
        }
        if($params['user_id'])
        {
            $filter['user_id'] = $params['user_id'];
        }

        $aftersalesInfo = kernel::single('sysaftersales_data')->getAftersalesInfo($params['fields'], $filter);

        if( $aftersalesInfo['sendback_data'] )
        {
            $logistics = unserialize($aftersalesInfo['sendback_data']);
            $aftersalesInfo['returnGoodsLogistics'] = $logistics;
        }

        if( $aftersalesInfo && $params['fields']['extends']['attachment'])
        {
            if( $aftersalesInfo['evidence_pic'] )
            {
                $aftersalesInfo['attachment'] = $this->__getEvidencePicUrl($params['aftersales_bn']);
            }
        }

        return $aftersalesInfo;
    }

    private function __getEvidencePicUrl($aftersalesBn)
    {
        $url = kernel::base_url(1).kernel::url_prefix().'/api?';

        $params['method'] = 'aftersales.download.evidencePic';
        $params['timestamp'] = time();
        $params['format'] = 'json';
        $params['v'] = 'v1';
        $params['sign_type'] = 'MD5';
        $params['aftersales_bn'] = $aftersalesBn;
        $params['sign'] = base_rpc_validate::sign($params,base_certificate::token());

        $url.= http_build_query($params);
        return $url;
    }

}


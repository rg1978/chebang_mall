<?php
/**
 * topapi
 *
 * -- logistics.list.get
 * -- 获取快递公司列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_logistics_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取快递公司列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        return [];
    }

    /**
     *
     * @return string corp_code 快递公司编码
     * @return string corp_name 快递公司名称
     */
    public function handle($params)
    {
        $params['fields'] = "corp_code,corp_name";
        $params['page_no'] = 1;
        $params['page_size'] = 100;
        $corpData = app::get('topapi')->rpcCall('logistics.dlycorp.get.list',$params);
        $return['list'] = $corpData['data'];
        return $return;
    }

    public function returnJson()
    {
        return '';
    }
}

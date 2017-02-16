<?php
/**
 * topapi
 *
 * -- region.json
 * -- 地区JSON数据
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_regionJson implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '地区JSON数据';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [];
    }

    /**
     * @return json region 返回地区的json格式数据
     */
    public function handle($params)
    {
        $staticsHostUrl = kernel::get_app_statics_host_url();
        $data['region'] = json_decode(file_get_contents($staticsHostUrl.'/ectools/statics/scripts/region.json'),true);
        return $data;
    }
}


<?php
/**
 * topapi
 *
 * -- member.address.get
 * -- 获取单个收货地址详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_address_get implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取单个收货地址详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'addr_id'  => ['type'=>'string', 'valid'=>'required|numeric', 'example'=>'', 'desc'=>'收货地址ID'],
        ];
    }

    /**
     * @return int addr_id 收货地址addr_id
     * @return string name 收货人姓名
     * @return string area 所在地区
     * @return string addr 街道地址，详细地址
     * @return string zip 邮政编码
     * @return int mobile 手机号
     * @return string def_addr 是否为默认地址 1为默认
     * @return string region_id 所在地区ID
     * @return string addrdetail 收货地址完整描述
     */
    public function handle($params)
    {
        $addrInfo = app::get('topapi')->rpcCall('user.address.info',$params);
        list($regions,$region_id) = explode(':', $addrInfo['area']);
        $addrInfo['area'] = $regions;
        $addrInfo['region_id'] = str_replace('/', ',', $region_id);
        $addrInfo['addrdetail'] = $addrInfo['area'].'/'.$addrInfo['addr'];

        return $addrInfo;
    }
}




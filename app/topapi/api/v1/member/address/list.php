<?php
/**
 * topapi
 *
 * -- member.address.list
 * -- 获取收货地址列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_address_list implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取收货地址列表';

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
     * @return int count.nowcount 当前已有收货地址数量
     * @return int count.maxcount 可拥有收货地址最大数量
     * @return string list.addr_id 收货地址addr_id
     * @return string list.name 收货人姓名
     * @return string list.area 所在地区
     * @return string list.addr 街道地址，详细地址
     * @return string list.zip 邮政编码
     * @return string list.mobile 手机号
     * @return string list.addrdetail 完整的详细收货地址
     * @return string list.region_id 地区ID
     * @return string list.def_addr 是否为默认地址 1为默认
     */
    public function handle($params)
    {
        $list = app::get('topapi')->rpcCall('user.address.list',$params);
        if( $list['list'] )
        {
            foreach( $list['list'] as &$row )
            {
                list($regions,$region_id) = explode(':', $row['area']);
                $row['area'] = $regions;
                $row['region_id'] = str_replace('/', ',', $region_id);
                $row['addrdetail'] = str_replace('/', '', $regions).$row['addr'];
            }
        }
        return $list;
    }
}


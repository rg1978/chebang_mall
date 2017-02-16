<?php
/**
 * topapi
 *
 * -- member.address.update
 * -- 更新收货地址
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_address_update implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新收货地址';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'addr_id'  => ['type'=>'string', 'valid'=>'required|numeric', 'example'=>'', 'desc'=>'收货地址ID'],
            'area'  => ['type'=>'string', 'valid'=>'required', 'example'=>'', 'desc'=>'所在地区', 'msg'=>'请选择所在地区'],
            'addr'  => ['type'=>'string', 'valid'=>'required', 'example'=>'桂林路396号', 'desc'=>'街道地址', 'msg'=>'请填写街道地址'],
            'name' => ['type'=>'string', 'valid'=>'required', 'example'=>'李四', 'desc'=>'收货人姓名', 'msg'=>'请填写收件人姓名'],
            'mobile' => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918765456', 'desc'=>'手机号','msg'=>'请填写手机号码|请填写正确的手机号码'],
            'zip' => ['type'=>'string', 'valid'=>'numeric|max:999999', 'example'=>'', 'desc'=>'邮政编码','msg'=>'邮编必须为6位数的整数|请填写正确的邮政编码'],
            'def_addr' => ['type'=>'string', 'valid'=>'in:0,1', 'example'=>'1', 'desc'=>'是否设为默认地址','msg'=>'设置默认地址参数错误'],
        ];
    }

    /**
     * @return bool true 成功
     */
    public function handle($params)
    {
        $area = app::get('topapi')->rpcCall('logistics.area',array('area'=>$params['area']));
        if( !$area )
        {
            throw new LogicException('请选择正确的地区');
        }

        $areaId =  rtrim(str_replace(",","/", $params['area']),'/');
        $params['area'] = $area . ':' . $areaId;

        return app::get('topapi')->rpcCall('user.address.add',$params);
    }
}


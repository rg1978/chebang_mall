<?php
class sysuser_api_addUserAdress {

    /**
     * 接口作用说明
     */
    public $apiDescription = '会员地址添加';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'用户ID必填'],
            'addr_id' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'收货地址ID'],
            'area' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'地区'],
            'addr' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'地址'],
            'zip' => ['type'=>'string','valid'=>'numeric|max:999999', 'default'=>'', 'example'=>'', 'description'=>'邮编'],
            'name' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员名称'],
            'mobile' => ['type'=>'string','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'电话'],
            'def_addr' => ['type'=>'bool','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'是否是设为默认'],
        );

        return $return;
    }

    public function addUserAdress($apiData)
    {
        $objLibUserAddr =  kernel::single('sysuser_data_user_addrs');
        return $objLibUserAddr->saveAddrs($apiData);
    }
}

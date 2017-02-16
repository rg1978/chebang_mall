<?php
/**
 * topapi
 *
 * -- member.basics.update
 * -- 更新会员基本信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_updateBasics implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新会员基础资料';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'username'  => ['type'=>'string', 'valid'=>'max:10',   'example'=>'李四',       'desc'=>'真实姓名',    'msg'=>'姓名不能超过10个字'],
            'name'      => ['type'=>'string', 'valid'=>'max:10',   'example'=>'onexbbc',    'desc'=>'昵称',        'msg'=>'昵称不能超过10个字'],
            'birthday'  => ['type'=>'string', 'valid'=>'date',     'example'=>'2001-08-19', 'desc'=>'生日',        'msg'=>'请选择正确的日期'],
            'sex'       => ['type'=>'string', 'valid'=>'in:0,1,2', 'example'=>'1',          'desc'=>'性别 0女 1男 2保密','msg'=>'请选择其中一项'],
        ];
    }

    /**
     * @return bool true 成功
     */
    public function handle($params)
    {
        $userId = $params['user_id'];
        unset($params['user_id']);
        foreach( $params as $key=>$value )
        {
            $apiparams['data'][$key] = $value;
        }

        if( $apiparams['data'] )
        {
            $apiparams['data'] = json_encode($apiparams['data']);
            $apiparams['user_id'] = $userId;
            return app::get('topapi')->rpcCall('user.basics.update', $apiparams);
        }
        else
        {
            return true;
        }
    }
}


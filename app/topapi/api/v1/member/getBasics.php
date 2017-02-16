<?php
/**
 * topapi
 *
 * -- member.basics.get
 * -- 获取会员基本信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_getBasics implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取会员基本信息';

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
     * @return string login_account 用户名
     * @return string username 真实姓名
     * @return string name 昵称
     * @return string birthday 生日
     * @return string sex 性别 0女 1男 2保密
     * @return string grade_id 会员等级ID
     * @return string grade_name 会员等级名称
     */
    public function handle($params)
    {
        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$params['user_id']]);
        $result = [
            'login_account' => $userInfo['login_account'],
            'username' => $userInfo['username'],
            'name' => $userInfo['name'],
            'birthday' => $userInfo['birthday'],
            'sex' => $userInfo['sex'],
            'grade_id' => $userInfo['grade_id'],
            'grade_name' => $userInfo['grade_name'],
        ];

        return $result;
    }
}


<?php
/**
 * ShopEx licence
 * - user.add.checkin.log
 * - 用于添加签到记录
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-23
 */
class sysuser_api_addCheckinLog{
    /**
     * 接口作用说明
     */
    public $apiDescription = '添加签到记录';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'用户ID必填'],
        );

        return $return;
    }

    /**
     * 获取用户签到记录
     * @return bool true 
     */
    public function add($apiData)
    {
        $checkinSetting = app::get('sysconf')->getConf('open.checkin');
        $userId = $apiData['user_id'];
        if ($checkinSetting) {
            $data = array(
                'user_id' => $userId,
                'checkin_date' => date('Y-m-d'),
                'checkin_time' => time(),
            );

            $objcheckin = app::get('sysuser')->model('user_checkin');
            $result = $objcheckin->insert($data);

            if(!$result)             {
                 throw new \LogicException('会员签到记录保存失败');   
            }
            event::fire('user.checkin',[$userId]);

            return $result;
        }
    }
}
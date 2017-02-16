<?php
/**
 * ShopEx licence
 * - user.get.checkin.info
 * - 用于获取用户签到记录
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-23
 */
class sysuser_api_getCheckinInfo{
    /**
     * 接口作用说明
     */
    public $apiDescription = '获取签到记录';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'用户ID必填'],
            'checkin_date' => ['type'=>'date','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'签到日期'],
        );

        return $return;
    }

    /**
     * 获取用户签到记录
     * @return int user_id 用户ID
     * @return date checkin_date 签到日期
     * @return int checkin_time 签到时间
     */

    public function getCheckinInfo($apiData)
    {
        $filter['user_id'] = $apiData['user_id'];
        $filter['checkin_date'] = $apiData['checkin_date'];
        $objMdlCheckinLog = app::get('sysuser')->model('user_checkin');
        $CheckinData = $objMdlCheckinLog->getList('*',$filter);
        return $CheckinData;
    }

}
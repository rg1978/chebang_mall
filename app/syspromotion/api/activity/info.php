<?php

/**
 * ShopEx licence
 * - promotion.activity.info
 * - 用于获取活动详情
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-18
 */
class syspromotion_api_activity_info{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = "获取活动详情";

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $data['params'] = array(
            'activity_id' => ['type'=>'int',        'valid'=>'required|integer', 'title'=>'活动id',  'example'=>'', 'desc'=>'活动id'],
            'fields'      => ['type'=>'field_list', 'valid'=>'',                 'title'=>'查询字段', 'example'=>'', 'desc'=>'查询字段'],
        );
        return $data;
    }

    /**
     * 获取活动详情
     * @desc 用于获取活动详情
     * @return int activity_id 活动ID
     * @return string activity_name 活动名称
     * @return string activity_tag 活动标签
     * @return string activity_desc 活动描述
     * @return timestamp apply_begin_time 申请活动开始时间
     * @return timestamp apply_end_time 申请活动结束时间
     * @return timestamp release_time 发布时间
     * @return timestamp start_time 活动生效开始时间
     * @return timestamp end_time 活动生效结束时间
     * @return int buy_limit 用户限购数量
     * @return int enroll_limit 店铺报名限制数量
     * @return array limit_cat 报名类目限制
     * @return array shoptype 店铺类型限制
     * @return number discount_min 最小折扣
     * @return number discount_max 最大折扣
     * @return bool mainpush 是否主推活动
     * @return string slide_images 活动主广告图
     * @return bool enabled 是否启用
     * @return timestamp created_time 创建时间
     * @return bool remind_enabled 是否启用开售提醒
     * @return string remind_way 提醒方式
     * @return timestamp remind_time 提前提醒时间
     */
    public function getInfo($params)
    {
        $filter['activity_id'] = $params['activity_id'];

        $row = "activity_id,activity_name,activity_tag,shoptype,release_time";
        if($params['fields'])
        {
            $row = $params['fields'];
        }

        $objActivity = kernel::single('syspromotion_activity');
        $dataInfo = $objActivity->getInfo($row,$filter);

        return $dataInfo;
    }
}

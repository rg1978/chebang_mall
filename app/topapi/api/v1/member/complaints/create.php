<?php
/**
 * topapi
 *
 * -- member.complaints.create
 * -- 投诉商家
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_complaints_create implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '投诉商家';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'oid'               => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'1507021608160001','desc'=>'子订单ID'],
            'complaints_type'   => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','desc'=>'投诉类型', 'msg'=>'请选择投诉类型'],
            'tel'               => ['type'=>'string', 'valid'=>'required|mobile', 'default'=>'', 'example'=>'13918765438','desc'=>'联系手机号', 'msg'=>'联系方式必填|请输入正确的手机号'],
            'content'           => ['type'=>'string', 'valid'=>'required|min:5|max:300', 'default'=>'', 'example'=>'申请退货被拒绝','desc'=>'投诉内容', 'msg'=>'请填写投诉原因|投诉原因5-300个字|投诉原因5-300个字'],
            'image_url'         => ['type'=>'string','valid'=>'', 'example'=>'', 'desc'=>'投诉图片凭证URL，最多5张图片，URL用逗号隔开'],
        );

        return $return;
    }

    /**
     * @return boolean true 投诉成功
     */
    public function handle($params)
    {
        return app::get('topwap')->rpcCall('trade.order.complaints.create', $params);
    }
}

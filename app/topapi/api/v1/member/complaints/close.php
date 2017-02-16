<?php
/**
 * topapi
 *
 * -- member.complaints.close
 * -- 撤销投诉
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_complaints_close implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '撤销投诉';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'complaints_id'       => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'15','desc'=>'投诉id' ],
            'buyer_close_reasons' => ['type'=>'string', 'valid'=>'required|min:5|max:200', 'default'=>'', 'example'=>'商家已经处理了','desc'=>'撤销投诉原因', 'msg'=>'请填写撤销原因|撤销原因最少5个字|撤销原因最多200个字'],
        );

        return $return;
    }

    /**
     * @return boolean true 撤销投诉成功
     */
    public function handle($params)
    {
        return app::get('topwap')->rpcCall('trade.order.complaints.buyer.close', $params);
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":true}';
    }
}

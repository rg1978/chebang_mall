<?php
/**
 * topapi
 *
 * -- trade.cancel.get
 * -- 获取会员取消订单详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_cancel_get implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取会员取消订单详情';

    /**
     * 定义API传入的应用级参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'cancel_id'    => ['type'=>'int', 'valid'=>'required|numeric',  'example'=>'', 'desc'=>'取消ID'],
        );

        return $return;
    }

    public function handle($params)
    {
        $cancelId = $params['cancel_id'];
        $data = app::get('topapi')->rpcCall('trade.cancel.get',['user_id'=>$params['user_id'],'cancel_id'=>$cancelId]);
        return $data;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"cancel_id":22,"user_id":4,"shop_id":1,"tid":1609061043500004,"pay_type":"online","payed_fee":"219.000","reason":"商品价格较贵","shop_reject_reason":null,"cancel_from":"buyer","process":"3","refunds_status":"SUCCESS","created_time":1473129824,"modified_time":1473129881,"log":[{"log_id":143,"rel_id":22,"op_id":4,"op_name":null,"op_role":"buyer","behavior":"cancel","log_text":"您的申请已提交","log_time":1473129824},{"log_id":144,"rel_id":22,"op_id":null,"op_name":null,"op_role":"1","behavior":"cancel","log_text":"商家同意退款，等待退款处理！","log_time":1473129863},{"log_id":145,"rel_id":22,"op_id":null,"op_name":null,"op_role":"1","behavior":"cancel","log_text":"取消订单成功，退款已处理！","log_time":1473129881}]}}';
    }
}

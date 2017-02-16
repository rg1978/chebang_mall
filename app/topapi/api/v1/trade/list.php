<?php
/**
 * topapi
 *
 * -- trade.list
 * -- 会员订单列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员订单列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'status'    => ['type'=>'string', 'valid'=>'in:WAIT_BUYER_PAY,WAIT_SELLER_SEND_GOODS,WAIT_BUYER_CONFIRM_GOODS,WAIT_RATE',  'example'=>'', 'desc'=>'订单状态'],
            'page_no'   => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'string', 'valid'=>'',  'example'=>'', 'desc'=>'需返回的字段'],
        );

        return $return;
    }

    public function handle($params)
    {
        $apiParams = array(
            'user_id' => $params['user_id'],
            'status' => $params['status'],
            'page_no' =>intval($params['page_no']) ? intval($params['page_no']) : 1,
            'page_size' =>intval($params['page_size']) ? intval($params['page_size']) : 10,
            'order_by' =>'created_time desc',
        );

        if( $params['status'] == 'WAIT_RATE' )
        {
            $apiParams['buyer_rate'] = 0;
        }

        if( !$params['fields'] ||  $params['fields'] == '*' )
        {
            $apiParams['fields'] = 'order.spec_nature_info,tid,shop_id,user_id,status,cancel_status,payment,points_fee,total_fee,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.item_id,order.shop_id,order.status,order.spec_nature_info,activity,pay_type,order.sendnum';
        }
        else
        {
            $apiParams['fields'] = $params['fields'];
        }

        $tradelist = app::get('topapi')->rpcCall('trade.get.list',$apiParams);

        if( $tradelist['list'] )
        {
            foreach( $tradelist['list'] as $row )
            {
                $list['list'][] = $row;
            }
            $list['pagers']['total'] = $tradelist['count'];
        }

        return $list;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"tid":1608151930150004,"shop_id":3,"user_id":4,"status":"WAIT_SELLER_SEND_GOODS","cancel_status":"NO_APPLY_CANCEL","payment":"2400.000","points_fee":"0.000","total_fee":"2388.000","post_fee":"12.000","payed_fee":"2400.000","receiver_name":"shopex","created_time":1471260638,"receiver_mobile":"13918087430","discount_fee":"0.000","need_invoice":0,"adjust_fee":"0.000","buyer_rate":0,"pay_type":"online","order":[{"spec_nature_info":null,"title":"华为 HUAWEI WATCH 动感系列 智能手表（黑色平尾","price":"2388.000","num":1,"pic_path":"http://images.bbc.shopex123.com/images/c9/cf/5f/896eceb25e1b37fca24552caeee0fb4a0df6510b.png","tid":1608151930150004,"oid":1608151930160004,"aftersales_status":null,"complaints_status":"NOT_COMPLAINTS","item_id":91,"shop_id":3,"status":"WAIT_SELLER_SEND_GOODS","sendnum":0}]}],"pagers":{"total":20}}}';
    }
}

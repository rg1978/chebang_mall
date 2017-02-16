<?php
/**
 * topapi
 *
 * -- member.complaints.list
 * -- 会员中心我的投诉列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_complaints_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员中心我的投诉列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'page_no'   => ['type'=>'int',    'valid'=>'numeric',  'example'=>'1', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'numeric',  'example'=>'10', 'desc'=>'每页数据条数,默认10条'],
        );

        return $return;
    }

    /**
     * @return int complaints_id 投诉ID
     * @return int oid 子订单ID
     * @return string complaints_type 投诉类型
     * @return string status 投诉状态
     * @return timestamp created_time 投诉创建时间
     * @return string item_id 投诉商品ID
     * @return string title 投诉商品标题
     * @return string pic_path 投诉商品图片
     * @return string status_desc 投诉状态说明
     */
    public function handle($params)
    {
        $params['fields'] = 'complaints_id,oid,complaints_type,status,created_time';
        $params['page_no'] = $params['page_no'] ?: 1;
        $params['page_size'] = $params['page_size'] ?: 10;

        $complaintsListData = app::get('topapi')->rpcCall('trade.order.complaints.list', $params);

        if( $complaintsListData['list'] )
        {
            $oids = [];
            foreach($complaintsListData['list'] as $complaint)
            {
                $oids[] = $complaint['oid'];
            }
        }

        $oids = implode(',', $oids);
        $orders = app::get('topapi')->rpcCall('trade.order.list.get', ['oids'=>$oids,'fields'=>'oid,title,pic_path,item_id']);
        $fmtOrders = [];
        foreach($orders as $order)
        {
            $orderId = $order['oid'];
            $fmtOrders[$orderId] = $order;
        }

        foreach($complaintsListData['list'] as $key=>$complaint)
        {
            $oid = $complaint['oid'];
            $complaintsListData['list'][$key]['item_id']    = $fmtOrders[$oid]['item_id'];
            $complaintsListData['list'][$key]['title']    = $fmtOrders[$oid]['title'];
            $complaintsListData['list'][$key]['pic_path'] = $fmtOrders[$oid]['pic_path'];
            $status = $complaint['status'];
            $complaintsListData['list'][$key]['status_desc'] = $this->complaints_type[$status];
        }

        $return['list'] = $complaintsListData['list'];
        $return['pagers']['total'] = $complaintsListData['count'];

        return $return;
    }

    private $complaints_type = array(
                'WAIT_SYS_AGREE' => '等待处理',
                'FINISHED' => '已完成',
                'BUYER_CLOSED' => '买家撤销投诉',
                'CLOSED' => '平台关闭投诉',
            );

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"complaints_id":1,"oid":1608041129430004,"complaints_type":"退货/换货问题","status":"WAIT_SYS_AGREE","created_time":1472019701,"title":"ONLY冬装新品宽松圆领底摆开叉设计针织连衣裙女","pic_path":"http://images.bbc.shopex123.com/images/29/e5/22/670cf312b0aaace1ebf6305d6f346ee147f29c16.jpg","status_desc":"等待处理"}],"pagers":{"total":1}}}';
    }
}

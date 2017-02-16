<?php
class topwap_data_member_complaints
{
    private $complaints_type = array(
                'WAIT_SYS_AGREE' => '等待处理',
                'FINISHED' => '已完成',
                'BUYER_CLOSED' => '买家撤销投诉',
                'CLOSED' => '平台关闭投诉',
            );

    //获取最基本的列表
    private function __getComplaintsList($userId, $pageSize = 10, $pageNumber = 1, $fields)
    {
       $params = array(
                'user_id'   => $userId,
                'fields'    => $fields,
                'page_no'   => intval($pageNumber),
                'page_size' => intval($pageSize),
        );

        $complaintsListData = app::get('topwap')->rpcCall('trade.order.complaints.list', $params);
        return $complaintsListData;
    }

    private function __getOrderList($oids, $fields, $isRow = false)
    {
        $orders = app::get('topwap')->rpcCall('trade.order.list.get', ['oids'=>$oids,'fields'=>$fields]);
        if($isRow)
            return $orders[0];
        return $orders;
    }

    //获取会员中心的列表页需要的列表
    public function getList($userId, $pageSize = 10, $pageNumber = 1)
    {
        $fields = 'complaints_id,oid,complaints_type,status,created_time';
        $complaintsListData = $this->__getComplaintsList($userId, $pageSize, $pageNumber, $fields);;

        if($complaintsListData['count'] == 0)
            return [];
        $oids = [];
        foreach($complaintsListData['list'] as $complaint)
        {
            $oids[] = $complaint['oid'];
        }

        $oids = implode(',', $oids);
        $orders = app::get('topwap')->rpcCall('trade.order.list.get', ['oids'=>$oids,'fields'=>'oid,title,pic_path']);
        $fmtOrders = [];
        foreach($orders as $order)
        {
            $orderId = $order['oid'];
            $fmtOrders[$orderId] = $order;
        }

        foreach($complaintsListData['list'] as $key=>$complaint)
        {
            $oid = $complaint['oid'];
            $complaintsListData['list'][$key]['title']    = $fmtOrders[$oid]['title'];
            $complaintsListData['list'][$key]['pic_path'] = $fmtOrders[$oid]['pic_path'];
            $status = $complaint['status'];
            $complaintsListData['list'][$key]['status'] = $this->complaints_type[$status];
        }

        return $complaintsListData;
    }

    public function getRow($filter, $userId, $fields)
    {
        if( empty($filter) ) return array();

        $filter['fields'] = $fields;
        $complaints = app::get('topwap')->rpcCall('trade.order.complaints.info', $filter, 'buyer');
        $status = $complaints['status'];
        $complaints['status'] = $this->complaints_type[$status];
        $complaints['status_val'] = $status;

        if($complaints['oid'])
        {
            $complaints['order'] = $this->__getOrderList($complaints['oid'], 'item_id,title,pic_path', true);
        }
        return $complaints;
    }

}


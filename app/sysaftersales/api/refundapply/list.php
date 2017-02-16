<?php

class sysaftersales_api_refundapply_list {

    public $apiDescription = "获取退款申请单列表";

    public function getParams()
    {
        $return['params'] = array(
            'fields'=> ['type'=>'field_list','valid'=>'required', 'description'=>'获取退款申请单需要返回的字段'],
            'shop_id' => ['type'=>'int','valid'=>'', 'description'=>'店铺ID'],
            'oid' => ['type'=>'string','valid'=>'', 'description'=>'子订单号集合'],
            'tid' => ['type'=>'string','valid'=>'', 'description'=>'主订单号集合'],
            'refunds_type' => ['type'=>'int','valid'=>'int', 'description'=>'退款类型'],
            'status' => ['type'=>'string','valid'=>'', 'description'=>'退款状态'],
            'page_no' => ['type'=>'int','valid'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int','valid'=>'', 'description'=>'每页数据条数,默认50条'],
            'orderBy' => ['type'=>'string','valid'=>'', 'description'=>'排序，默认modified_time desc'],
            'modified_time_start' => ['type'=>'json_encode','valid'=>'', 'description'=>'最后修改时间开始,有开始时间必须要有结束时间'],
            'modified_time_end' => ['type'=>'json_encode','valid'=>'', 'description'=>'最后修改时间结束'],
        );
        return $return;
    }

    private function __preParams($params)
    {
        if($params['shop_id'])
        {
            $filter['shop_id'] = $params['shop_id'];
        }

        if( $params['modified_time_start'] && $params['modified_time_end'] )
        {
            $filter['modified_time|bthan'] = $params['modified_time_start'];
            $filter['modified_time|lthan'] = $params['modified_time_end'];
        }

        if($params['tid'])
        {
            $filter['tid'] = explode(',',$params['tid']);
        }

        if($params['oid'])
        {
            $filter['oid'] = explode(',',$params['oid']);
        }

        if(isset($params['refunds_type']))
        {
            $filter['refunds_type'] = $params['refunds_type'];
        }

        if(isset($params['status']))
        {
            $filter['status'] = explode(',',$params['status']);
        }

        return $filter;
    }

    public function get($params)
    {
        $filter = $this->__preParams($params);

        $total = app::get('sysaftersales')->model('refunds')->count($filter);
        if( !$total ) return ['list'=>array(),'total'=>0];

        $pageTotal = ceil($total/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = $pageTotal < $page ? $totalPage : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy = $params['orderBy'] ? $params['orderBy'] : 'modified_time desc';

        $listData = app::get('sysaftersales')->model('refunds')->getList($params['fields'], $filter, $offset, $limit, $orderBy);

        $data['list'] = $listData;
        $data['total'] = $total;
        return $data;
    }
}


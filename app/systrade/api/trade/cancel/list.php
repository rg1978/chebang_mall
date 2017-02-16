<?php

class systrade_api_trade_cancel_list {

    public $apiDescription = "获取取消订单列表";

    public function getParams()
    {
        $return['params'] = array(
            'fields'=> ['type'=>'field_list','valid'=>'required', 'description'=>'获取取消订单列表需要返回的字段'],
            'user_id' => ['type'=>'int','valid'=>'', 'description'=>'会员ID'],
            'shop_id' => ['type'=>'int','valid'=>'', 'description'=>'店铺ID'],
            'tid' => ['type'=>'string','valid'=>'', 'description'=>'订单编号'],
            'refunds_status' => ['type'=>'string','valid'=>'', 'description'=>'退款状态'],
            'process' => ['type'=>'string','valid'=>'', 'description'=>'处理进度'],
            'page_no' => ['type'=>'int','valid'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int','valid'=>'', 'description'=>'每页数据条数,默认50条'],
            'orderBy' => ['type'=>'string','valid'=>'', 'description'=>'排序，默认modified_time desc'],
            'created_time_start' => ['type'=>'json_encode','valid'=>'', 'description'=>'创建时间开始,有开始时间必须要有结束时间'],
            'created_time_end' => ['type'=>'json_encode','valid'=>'', 'description'=>'创建时间结束'],
            'modified_time_start' => ['type'=>'json_encode','valid'=>'', 'description'=>'最后修改时间开始,有开始时间必须要有结束时间'],
            'modified_time_end' => ['type'=>'json_encode','valid'=>'', 'description'=>'最后修改时间结束'],
        );
        return $return;
    }

    private function __preParams($params)
    {
        $column = ['user_id','shop_id','refunds_status','process','tid'];
        foreach( $column as $col )
        {
            if( $params[$col] )
            {
                $filter[$col] = $params[$col];
            }
        }

        if( $params['created_time_start'] && $params['created_time_end'] )
        {
            $filter['created_time|bthan'] = $params['created_time_start'];
            $filter['created_time|lthan'] = $params['created_time_end'];
        }

        if( $params['modified_time_start'] && $params['modified_time_end'] )
        {
            $filter['modified_time|bthan'] = $params['modified_time_start'];
            $filter['modified_time|lthan'] = $params['modified_time_end'];
        }

        return $filter;
    }

    public function get($params)
    {
        $filter = $this->__preParams($params);

        $total = app::get('systrade')->model('trade_cancel')->count($filter);
        if( !$total ) return ['list'=>array(),'total'=>0];

        $pageTotal = ceil($total/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = $pageTotal < $page ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy = $params['orderBy'] ? $params['orderBy'] : 'modified_time desc';

        $tradecancelList = app::get('systrade')->model('trade_cancel')->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $tids = array_column($tradecancelList,'tid');
        $listData = array_bind_key($tradecancelList, 'tid');

        $orderList = app::get('systrade')->model('order')->getList('tid,oid,title,pic_path,item_id,sku_id,spec_nature_info,num,gift_data',['tid'=>$tids]);
        foreach( $orderList as $row )
        {
            if( $listData[$row['tid']] )
            {
                $listData[$row['tid']]['order'][] = $row;
            }
        }

        $data['list'] = $listData;
        $data['total'] = $total;
        return $data;
    }
}


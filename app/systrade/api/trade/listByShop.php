<?php
class systrade_api_trade_listByShop {

    public $apiDescription = "获取订单列表";

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'店铺id'],
            'user_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单所属用户id'],
            'status' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单状态'],
            'buyer_rate' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单评价状态'],
            'tid' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单编号,多个用逗号隔开'],
            'update_time_start' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'查询指定时间内的交易创建时间开始yyyy-MM-dd'],
            'update_time_end' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'查询指定时间内的交易创建时间结束yyyy-MM-dd'],
            'page_no' => ['type'=>'int','valid'=>'','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
            'order_by' => ['type'=>'int','valid'=>'','description'=>'排序方式','example'=>'','default'=>'created_time desc'],
            'fields' => ['type'=>'field_list','valid'=>'required','description'=>'获取的交易字段集','example'=>'','default'=>''],
        );

        $return['extendsFields'] = ['order'];
        return $return;
    }

    public function tradeList($params)
    {
        $tradeRow = $params['fields']['rows'];
        $orderRow = $params['fields']['extends']['order'];

        //lastmodify的范围划分
        if($params['update_time_start'] > 0 && $params['update_time_end'] > 0)
        {
            $params['modified_time|bthan'] = $params['update_time_start'];
            $params['modified_time|lthan'] = $params['update_time_end'];
        }
        unset($params['update_time_start']);
        unset($params['update_time_end']);

        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "created_time desc";
        }

        $pageNo = $params['page_no'];
        $pageSize = $params['page_size'];
        unset($params['fields'],$params['page_no'],$params['page_size'],$params['order_by'],$params['oauth']);

        foreach($params as $k=>$val)
        {
            if(!$val)
            {
                unset($params[$k]);
                continue;
            }
            if($k == "status" || $k == "tid")
            {
                $params[$k] = explode(',',$val);
            }
        }

        $objMdlTrade = app::get('systrade')->model('trade');
        $count = $objMdlTrade->count($params);

        //分页使用
        $page =  $pageNo ? $pageNo : 1;
        $limit = $pageSize ? $pageSize : 40;
        $pageTotal = ceil($count/$limit);
        $currentPage = $pageTotal < $page ? $totalPage : $page;
        $offset = ($currentPage-1) * $limit;

        $tradeLists = $objMdlTrade->getList($tradeRow,$params,$offset,$limit,$orderBy);
        $tradeLists = array_bind_key($tradeLists,'tid');
        if($orderRow && $tradeLists)
        {
            $orderRow = str_append($orderRow,'tid');
            $objMdlOrder = app::get('systrade')->model('order');
            $tids = array_column($tradeLists,'tid');
            $orderLists = $objMdlOrder->getList($orderRow,array('tid'=>$tids));
            foreach($orderLists as $key=>$value)
            {
                $tradeLists[$value['tid']]['order'][] = $value;
            }
        }

        $trade['list'] = $tradeLists;
        $trade['count'] = $count;
        return $trade;
    }
}




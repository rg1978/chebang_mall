<?php
class sysclearing_api_getSettlementList{
    public $apiDescription = "获取结算单汇总列表";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'店铺编号id'],
            'settlement_time_than' => ['type'=>'string', 'valid'=>'', 'default'=>'1', 'example'=>'','description'=>'生成结算单开始时间'],
            'settlement_time_lthan' => ['type'=>'string', 'valid'=>'', 'default'=>'1', 'example'=>'','description'=>'生成结算单结束时间'],
            'settlement_status' => ['type'=>'string', 'valid'=>'', 'default'=>'1', 'example'=>'','description'=>'结算状态'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
        );
        return $return;
    }
    public function getList($params)
    {
        if($params['settlement_time_than'])
        {
            $filter['settlement_time|than']  = $params['settlement_time_than'];
        }

        if($params['settlement_time_lthan'])
        {
            $filter['settlement_time|lthan']  = $params['settlement_time_lthan'];
        }

        if($params['settlement_status'])
        {
            $filter['settlement_status'] = $params['settlement_status'];
        }

        $filter['shop_id'] = $params['shop_id'];

        $objMdlSettlement = app::get('sysclearing')->model('settlement');
        $count = $objMdlSettlement->count($filter);

        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $pageTotal = ceil($count/$limit);
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' settlement_time desc';
        $settlement['list'] = $objMdlSettlement->getList('*', $filter,$offset,$limit,$orderBy);
        $settlement['count'] = $count;
        return $settlement;
    }
}



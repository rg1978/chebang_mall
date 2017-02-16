<?php

class systrade_api_getOrderListByItem {

    /**
     * 接口作用说明
     */
    public $apiDescription = '根据商品信息获取子订单列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单所属用户id'],
            'item_id' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'商品id(和oids至少有一个必填)'],
            'keyword' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'子订单商品搜索关键字'],
            'status' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'子订单状态，如果多个状态用逗号隔开'],
            'page_no' => ['type'=>'int','valid'=>'int', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int','valid'=>'int', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认100条'],
            'order_by' => ['type'=>'int','valid'=>'','description'=>'排序方式','example'=>'','default'=>'created_time desc'],
            'fields'=> ['type'=>'field_list','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'获取单个子订单需要返回的字段'],
        );
        $return['extendsFields'] = ['order','activity'];
        return $return;
    }

    /**
     * 获取订单列表
     *
     * @param array $params 接口传入参数
     * @return array
     */
    public function getData($params)
    {
        $orderList = array();
        if($params['oauth']['account_id'] && $params['oauth']['auth_type'] == "member" )
        {
            $filter['user_id'] = $params['oauth']['account_id'];
        }else{
            $filter['user_id'] = $params['user_id'];
        }
        if($params['item_id'])
        {
            $filter['item_id'] = $params['item_id'];
        }
        if($params['keyword'])
        {
            $filter['title|has'] = $params['keyword'];
        }
        if($params['status'])
        {
            $filter['status'] = explode(',',$params['status']);
        }

        //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 40;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;
        $max = 1000000;
        if($pageSize >= 1 && $pageSize < 500 && $pageNo >=1 && $pageNo < 200 && $pageSize*$pageNo < $max)
        {
            $limit = $pageSize;
            $page = ($pageNo-1)*$limit;
        }

        $tradeRow = $params['fields']['rows'];
        $orderRow = $params['fields']['extends']['order'];
        $activityRow = $params['fields']['extends']['activity'];

        //订单数量
        $objMdlOrder = app::get('systrade')->model('order');
        $count = $objMdlOrder->count($filter);
        $oids = $objMdlOrder->getList('oid', $filter);
        $oids = array_unique(array_column($oids, 'oid'));
        $tradeLists = array();

        //获取订单列表
        if($orderRow && $count)
        {
            //获取子订单列表
            $orderRow = str_append($orderRow,'tid');
            $objMdlOrder = app::get('systrade')->model('order');
            $orderLists = $objMdlOrder->getList($orderRow,$filter, $page,$limit);

            //获取订单
            $tids = array_unique(array_column($orderLists, 'tid'));
            if($tids){
                $tradeFields = $params['fields'];
                $tradeParams = array();
                $tradeParams['tid'] = $tids;
                $orderBy = $params['order_by'];
                $objMdlTrade = app::get('systrade')->model('trade');
                $tradeLists = $objMdlTrade->getList($tradeRow,$tradeParams,0,-1,$orderBy);
                $tradeLists = array_bind_key($tradeLists,'tid');
            }

            //是否需要显示标签促销tag
            if( $activityRow && $orderLists )
            {
                $oids = array_column($orderLists,'oid');
                $promotionActivityData = app::get('systrade')->model('promotion_detail')->getList('promotion_tag,oid',['promotion_type'=>'activity','oid'=>$oids]);
                //一个子订单只可参加一次标签促销活动
                $promotionActivityData = array_bind_key($promotionActivityData,'oid');
            }

            //组合数据
            foreach($orderLists as $key=>$value)
            {
                if( $promotionActivityData[$value['oid']]['promotion_tag'] )
                {
                    $value['promotion_tag'] = $promotionActivityData[$value['oid']]['promotion_tag'];
                }
                $tradeLists[$value['tid']]['order'][] = $value;
            }

        }

        $trade['list'] = $tradeLists;
        $trade['count'] = $count;

        return $trade;
    }
}


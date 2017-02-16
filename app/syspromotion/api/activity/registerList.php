<?php

/**
 * ShopEx licence
 * - promotion.activity.register.list
 * - 用于获取活动报名列表
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-18
 */
class syspromotion_api_activity_registerList{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = "获取活动报名列表";

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $data['params'] = array(
            'activity_id'     => ['type'=>'int',        'valid'=>'int',    'title'=>'活动id',     'example'=>'', 'desc'=>'活动id'],
            'shop_id'         => ['type'=>'int',        'valid'=>'int',    'title'=>'店铺id',     'example'=>'', 'desc'=>'店铺id'],
            'valid_status'    => ['type'=>'int',        'valid'=>'int',    'title'=>'有效状态',    'example'=>'', 'desc'=>'有效状态'],
            'activity_status' => ['type'=>'int',        'valid'=>'string', 'title'=>'活动状态',    'example'=>'', 'desc'=>'活动状态（已开始报名[starting]、活动已结束[end]）'],
            'page_no'         => ['type'=>'int',        'valid'=>'int',    'title'=>'当前页码',    'example'=>'', 'desc'=>'分页当前页码,1<=no<=499'],
            'page_size'       => ['type'=>'int',        'valid'=>'int',    'title'=>'分页每页条数', 'example'=>'', 'desc'=>'分页每页条数(1<=size<=200)'],
            'order_by'        => ['type'=>'int',        'valid'=>'string', 'title'=>'排序方式',    'example'=>'', 'desc'=>'排序方式'],
            'fields'          => ['type'=>'field_list', 'valid'=>'',       'title'=>'排序方式',    'example'=>'', 'desc'=>'查询字段'],
        );
        return $data;
    }

    /**
     * 获取活动报名列表
     * @desc 用于获取活动报名列表
     * @return array data 活动报名列表
     * @return int data[].id 主键ID
     * @return int data[].shop_id 店铺ID
     * @return int data[].activity_id 活动ID
     * @return string data[].verify_status 审核状态
     * @return bool data[].valid_status 有效状态
     * @return string data[].refuse_reason 拒绝原因
     * @return timestamp data[].modified_time 报名最后更新时间
     * @return string count 返回数据数量
     */
    public function registerList($params)
    {
        $objMdlActivityRegister = app::get('syspromotion')->model('activity_register');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $filter = array('shop_id' => $params['shop_id']);
        $filter['valid_status'] = isset($params['valid_status']) ? $params['valid_status'] : 1;
        if($params['activity_id']!='')
        {
            $filter['activity_id'] = $params['activity_id'];
        }

        if(isset($params['activity_status']) && $params['activity_status'])
        {
            $objMdlActivity = app::get('syspromotion')->model('activity');
            if($params['activity_status'] =="starting")
            {
                $fr['end_time|bthan'] = time();
            }
            elseif($params['activity_status'] == "end")
            {
                $fr['end_time|sthan'] = time();
            }
            $data = $objMdlActivity->getList('activity_id',$fr);
            foreach($data as $val)
            {
                $filter['activity_id'][] = $val['activity_id'];
            }
        }

        $registerListCount = $objMdlActivityRegister->count($filter);
        //分页使用
        $pageTotal = ceil($registerListCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = $pageTotal < $page ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy = $params['order_by'];
        if(!$params['order_by'])
        {
            $orderBy = "modified_time desc";
        }

        $registerListData = $objMdlActivityRegister->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $result = array(
            'data' => $registerListData,
            'count' => $registerListCount,
        );

        return $result;
    }
}

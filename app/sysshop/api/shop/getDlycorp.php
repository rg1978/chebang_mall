<?php
class sysshop_api_shop_getDlycorp{

    public $apiDescription = "获取店铺签约物流";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' =>['type'=>'int','valid'=>'int', 'description'=>'店铺id','default'=>'','example'=>'1'],
            'corp_id' =>['type'=>'int','valid'=>'', 'description'=>'物流公司编号id','default'=>'','example'=>'1'],
            'fields' => ['type'=>'field_list','valid'=>'', 'description'=>'获取指定字段','default'=>'corp_id,corp_code,corp_name,shop_id','example'=>'corp_id,corp_code,corp_name'],
            'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1','default'=>'','example'=>''],
            'page_size' =>['type'=>'int','valid'=>'int','description'=>'分页每页条数(1<=size<=1000)','example'=>'','default'=>'500'],
            'order_by' => ['type'=>'int','valid'=>'','description'=>'排序方式','example'=>'','default'=>' order_sort asc'],
        );
        return $return;
    }
    public function getList($params)
    {
        if(!$params['shop_id'] && !$params['corp_id'])
        {
            return array();
        }

        if($params['shop_id'])
        {
            $filter['shop_id'] = $params['shop_id'];
        }

        if($params['corp_id'])
        {
            $filter['corp_id'] = explode(',',$params['corp_id']);
        }

        //默认查询字段
        $row = "corp_id,corp_code,corp_name,shop_id";
        if($params['fields'])
        {
            $row = $params['fields'];
        }

        $objMdlDlycorpShop = app::get('sysshop')->model('shop_rel_dlycorp');
        /*
        //分页使用
        $count = $objMdlDlycorpShop->count($filter);
        $pageTotal = ceil($count/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : -1;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;
         */

        //$pagedata['list'] = $objMdlDlycorpShop->getList($row,$filter,$offset, $limit);
        //$pagedata['count'] = $count;
        $pagedata['list'] = $objMdlDlycorpShop->getList($row,$filter);

        return $pagedata;
    }

}

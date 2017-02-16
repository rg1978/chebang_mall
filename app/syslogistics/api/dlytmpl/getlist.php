<?php
class syslogistics_api_dlytmpl_getlist{
    public $apiDescription = " 获取运费模板列表";
    public function getParams()
    {
        $return['params'] = array(
            'dlytmpl_ids' =>['type'=>'string','valid'=>'', 'description'=>'配送模板ids','default'=>'','example'=>'1'],
            'shop_id' =>['type'=>'string','valid'=>'', 'description'=>'店铺id','default'=>'','example'=>'1'],
            'status' =>['type'=>'string','valid'=>'', 'description'=>'模板状态','default'=>'on','example'=>'on'],
            'page_no' => ['type'=>'int','valid'=>'','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'','description'=>'分页每页条数(1<=size<=1000)','example'=>'','default'=>'500'],
            'orderBy' => ['type'=>'int','valid'=>'','description'=>'排序方式','example'=>'','default'=>' order_sort asc'],
            'fields' =>['type'=>'string','valid'=>'', 'description'=>'所需字段','default'=>'template_id,name','example'=>'name,status'],
        );
        return $return;
    }
    public function getList($params)
    {
        if($params['shop_id'])
        {
            $filter['shop_id'] = explode(',',$params['shop_id']);
        }
        if($params['status'])
        {
            $filter['status'] = $params['status'] ;
        }
        $row = "template_id,name";
        if($params['fields'])
        {
            $row = $params['fields'];
        }

         //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 500;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' modifie_time DESC';

        $objDataDlyTmpl = kernel::single('syslogistics_data_dlytmpl');
        $data = $objDataDlyTmpl->fetchDlyTmpl($row,$filter,$pageNo,$pageSize,$orderBy);
        return $data;
    }
}

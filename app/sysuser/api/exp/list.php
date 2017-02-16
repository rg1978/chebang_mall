<?php
class sysuser_api_exp_list{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取积分记录列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认100条'],
            'orderBy' => ['type'=>'int','valid'=>'','description'=>'排序方式','example'=>'','default'=>'modified_time desc'],
            'fields'=> ['type'=>'field_list','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段'],
        );

        return $return;
    }

    public function getList($params)
    {
        $filter['user_id'] = pamAccount::getAccountId();
        $objMdlUser = app::get('sysuser')->model('user');
        $objMdlUserExp = app::get('sysuser')->model('user_experience');

        //分页
        $count = $objMdlUserExp->count($filter);
        $pageTotal = ceil($count/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        //排序
        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "modified_time desc";
        }

        $data['datalist']['user'] = $objMdlUser->getRow('experience',$filter);

        if(!$params['fields'])
        {
            $params['fields'] = "*";
        }

        $data['datalist']['exp'] = $objMdlUserExp->getList($params['fields'],$filter,$offset,$limit,$orderBy);
        $data['totalnum'] = $count;
        return $data;
    }
}

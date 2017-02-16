<?php
class sysshop_api_applycat_getApplyList{
    public $apiDescription = "获取店铺申请的类目列表";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' =>['type'=>'int','valid'=>'int|required', 'description'=>'店铺id','default'=>'','example'=>'1'],
            'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1','default'=>'','example'=>''],
            'page_size' =>['type'=>'int','valid'=>'int','description'=>'分页每页条数(1<=size<=1000)','example'=>'','default'=>'500'],
            'orderBy' =>['type'=>'int','valid'=>'','description'=>'排序','example'=>'','default'=>'apply_id desc'],
            'fields' => ['type'=>'field_list','valid'=>'', 'description'=>'获取指定字段','default'=>'','example'=>''],
        );
        return $return;
    }
    public function getAppleyList($params)
    {

        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }

        $filter['shop_id'] = $params['shop_id'];

        $objMdlApplyCat = app::get('sysshop')->model('shop_apply_cat');

        $countApply = $objMdlApplyCat->count($filter);

        //分页使用
        $pageTotal = ceil($countApply/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' apply_id DESC';

        $applyList = $objMdlApplyCat->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $catIds = array_column($applyList,'cat_id');
        $catList = app::get('sysshop')->rpcCall('category.cat.get.info',['cat_id'=>implode(',',$catIds)]);
        foreach($applyList as $k=>$value)
        {
            if($catList[$value['cat_id']])
            {
                $applyList[$k]['cat_name'] = $catList[$value['cat_id']]['cat_name'];
            }
        }
        $result = array(
            'count' => $countApply,
            'list' => $applyList
        );
        return $result;
    }
}


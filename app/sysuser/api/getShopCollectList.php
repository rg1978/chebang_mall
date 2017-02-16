<?php
class sysuser_api_getShopCollectList {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取会员店铺收藏列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1','default'=>'','example'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认20条','default'=>'','example'=>''],
            'fields'=> ['type'=>'field_list','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
            'user_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'用户ID必填','default'=>'','example'=>''],
            'orderBy' => ['type'=>'string','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序','default'=>'','example'=>''],
        );

        return $return;
    }

    public function getShopCollectList($params)
    {
        $objMdlFav = app::get('sysuser')->model('shop_fav');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $filter = array('user_id'=>$params['user_id']);

        $shopCount = $objMdlFav->getcount($filter);
        $pageTotal = ceil($shopCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 40;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        $orderBy = $params['orderBy'] ? $params['orderBy'] : 'snotify_id DESC';

        $aData = $objMdlFav->getList($params['fields'], $filter, $offset,$limit, $orderBy);
        $shopData = array(
                'shopcollect' => $aData,
                'shopcount' => $shopCount,
            );
        //echo '<pre>';print_r($itemCount);exit();
        return $shopData;
    }
}

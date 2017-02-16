<?php
/**
 * 扣减库存
 * item.store.police
 */
class sysitem_api_item_storePolice{

    public $apiDescription = "库存报警";

    public function getParams()
    {
        $return['params'] = array(
            'store' => ['type'=>'int','valid'=>'required','description'=>'库存数','example'=>'2','default'=>''],
            'shop_id' => ['type'=>'string','valid'=>'','description'=>'店铺id','example'=>'18'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的商品字段集 item_id','example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
            'page_no' => ['type'=>'int','valid'=>'numeric','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'numeric','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
        );
        return $return;
    }

    public function storePolice($params)
    {
        //$skuPolice = app::get('sysconf')->getConf('trade.sku.police');

        if($params['fields'])
        {
            $row = $params['fields'];
        }
        else
        {
            $row = '*';
        }

        $filter['store'] = $params['store'];
        $filter['shop_id'] = $params['shop_id'];

        //分页使用
        $itemCount = kernel::single('sysitem_item_store')->getItemCountByStore($filter);
        $pageTotal = ceil($itemCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 40;
        $currentPage = ($pageTotal && $pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        //排序
        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "modified_time desc,list_time desc";
        }

        $itemList = kernel::single('sysitem_item_store')->getItemListByStore($row,$filter,$offset, $limit,$orderBy);
        $data['list'] = $itemList;
        $data['total_found'] = $itemCount;
        return $data;
        //echo '<pre>';print_r($itemList);exit();
    }
}



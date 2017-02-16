<?php
class sysshop_api_getShopByCat{
    public $apiDescription = "获取指定用户的店铺id";
    public function getParams()
    {
        $return['params'] = array(
            'cat_id' => ['type'=>'string','valid'=>'required','description'=>'类目id,多个用逗号(,)隔开','default'=>'','example'=>'1'],
            'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1','default'=>'','example'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认20条','default'=>'','example'=>''],
            'orderBy' => ['type'=>'string','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序','default'=>'shop_id desc','example'=>''],
            'fields'=> ['type'=>'field_list','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'shop_id','example'=>''],
        );
        return $return;
    }

    public function getShop($params)
    {
        $filter['cat_id'] = explode(',',$params['cat_id']);
        if(!$filter)
        {
            return array();
        }

        $row = $params['fields'];
        if(!$row)
        {
            $row = "shop_id";
        }

        //分页使用
        $limit = $params['page_size'] ? $params['page_size'] : 40;
        $page = $params['page_no'] ? $params['page_no'] : 1;

        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "shop_id desc";
        }

        $objShop = kernel::single('sysshop_data_shop');
        $result = $objShop->getShopByCat($row,$filter,$page,$limit,$orderBy);
        return $result;
    }

}

<?php
class sysitem_api_sku_search{
    public $apiDescription = '根据条件获取货品列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'string','valid'=>'','description'=>'商品id，多个id用，隔开','example'=>'2,3,5,6','default'=>''],
            'sku_id' => ['type'=>'string','valid'=>'','description'=>'商品id，多个id用，隔开','example'=>'2,3,5,6','default'=>''],
            'shop_id' => ['type'=>'int','valid'=>'integer','description'=>'店铺id','example'=>'','default'=>''],
            'search_keywords' => ['type'=>'string','valid'=>'','description'=>'搜索商品关键字','example'=>'','default'=>''],
            'min_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最小价格','example'=>'','default'=>''],
            'max_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最大价格','example'=>'','default'=>''],
            'bn' => ['type'=>'string','valid'=>'','description'=>'搜索商品货号','example'=>'','default'=>''],
            'shop_cat_id' => ['type'=>'int','valid'=>'string','description'=>'店铺自有类目id','example'=>'','default'=>''],
            'cat_id' => ['type'=>'string','valid'=>'','description'=>'商城类目id','example'=>'1,3','default'=>''],
            'brand_id' => ['type'=>'string','valid'=>'','description'=>'品牌ID','example'=>'1,2,3','default'=>''],

            'page_no' => ['type'=>'int','valid'=>'numeric','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'numeric','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
            'orderBy' => ['type'=>'string','valid'=>'','description'=>'排序方式','example'=>'','default'=>'modified_time desc,list_time desc'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的商品字段集','example'=>'','default'=>''],
        );
        $return['extendsFields'] = ['store','item','status'];
        return $return;
    }

    private function __getFilter($params)
    {
        $filterCols = ['sku_id','item_id','shop_id','shop_cat_id','cat_id','search_keywords','brand_id'];
        foreach( $filterCols as $col )
        {
            if( $params[$col] )
            {
                $params[$col] = trim($params[$col]);

                if( in_array($col,['sku_id','item_id','brand_id','shop_cat_id','prop_index']) )
                {
                    if( $col == 'shop_cat_id')
                    {
                        foreach( explode(',',$params[$col]) as $v)
                        {
                            $val = intval($v);
                            $shopCatId[]= ','. $val .',';
                        }
                        $params['shop_cat_id'] = $shopCatId;
                    }
                    else
                    {
                        $params[$col] = explode(',',$params[$col]);
                    }

                }
                $filter[$col] = $params[$col];
            }
        }
        if($params['cat_id'])
        {
            $filter['cat_id'] = explode(',', $params['cat_id']);
        }

        if($params['max_price'] && $params['min_price'])
        {
            $filter['price|between'] = [$params['min_price'],$params['max_price']];
        }
        elseif($params['max_price'] && !$params['min_price'])
        {
            $filter['price|sthan'] = $params['max_price'];
        }
        elseif (!$params['max_price'] && $params['min_price'])
        {
            $filter['price|bthan'] = $params['min_price'];
        }

        //商品货号
        if($params['bn'])
        {
            $filter['bn|has'] = $params['bn'];
        }

        return $filter;
    }

    public function getList($params)
    {
        $objMdlSku = app::get('sysitem')->model('sku');

        $row = $params['fields']['rows'];

        //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 40;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;
        $max = 1000000;
        if($pageSize >= 1 && $pageSize < 500 && $pageNo >=1 && $pageSize*$pageNo < $max)
        {
            $limit = $pageSize;
            $page = ($pageNo-1)*$limit;
        }

        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "modified_time desc";
        }

        $filter = $this->__getFilter($params);

        if($filter['search_keywords'])
        {
            $filter['title|has'] = $filter['search_keywords'];
        }

        $skuList = $objMdlSku->getList($row,$filter,$page,$limit,$orderBy);

        $data['list'] = array_bind_key($skuList,'sku_id');
        $data['total_found'] = $objMdlSku->count($filter);


        $skuIds = array_column($data['list'], 'sku_id');
        if( $skuIds && $params['fields']['extends']['store'] )
        {
            $skuStore = kernel::single('sysitem_item_info')->getSkusStore($skuIds);

            if( $skuStore )
            {
                foreach ($data['list'] as $key => &$value)
                {
                    $value['store'] = $skuStore[$value['sku_id']]['store'];
                    $value['freez'] = $skuStore[$value['sku_id']]['freez'];
                    $value['realStore'] = $skuStore[$value['sku_id']]['realStore'];
                }
            }
        }

        $itemIds = array_column($data['list'],'item_id');
        if($itemIds && $params['fields']['extends']['item'])
        {
            $itemList = kernel::single('sysitem_item_info')->getItemList($itemIds,'sub_stock,item_id,shop_id');
            $shopIds = array_column($itemList,'shop_id');
            foreach($data['list'] as $key => &$value)
            {
                $value = array_merge($value,$itemList[$value['item_id']]);
            }
        }

        if($itemIds && $params['fields']['extends']['status'])
        {
            $row = $params['fields']['extends']['status'];
            $itemStatus = kernel::single('sysitem_item_info')->getItemStatus($itemIds, $row);
            foreach($data['list'] as $key => &$value)
            {
                $value = array_merge($value,$itemStatus[$value['item_id']]);
            }
        }
        return $data;
    }
}

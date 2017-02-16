<?php
/**
 * 接口作用说明
 * item.search
 */
class sysitem_api_search_searchItems{

    public $apiDescription = '根据条件获取商品列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'string','valid'=>'','description'=>'商品id，多个id用，隔开','example'=>'2,3,5,6','default'=>''],
            'shop_id' => ['type'=>'int','valid'=>'integer','description'=>'店铺id','example'=>'','default'=>''],
            'dlytmpl_id' => ['type'=>'int','valid'=>'integer','description'=>'运费模板id','example'=>'','default'=>''],
            'shop_cat_id' => ['type'=>'int','valid'=>'string','description'=>'店铺自有类目id','example'=>'','default'=>''],
            'search_shop_cat_id' => ['type'=>'int','valid'=>'','description'=>'店铺搜索自有一级类目id','example'=>'','default'=>''],
            'cat_id' => ['type'=>'string','valid'=>'','description'=>'商城类目id','example'=>'1,3','default'=>''],
            'brand_id' => ['type'=>'string','valid'=>'','description'=>'品牌ID','example'=>'1,2,3','default'=>''],
            'prop_index' => ['type'=>'string','valid'=>'','description'=>'商品自然属性','example'=>'','default'=>''],
            'search_keywords' => ['type'=>'string','valid'=>'','description'=>'搜索商品关键字','example'=>'','default'=>''],
            'buildExcerpts' => ['type'=>'bool','valid'=>'','description'=>'是否关键字高亮','example'=>'','default'=>''],
            'is_selfshop' => ['type'=>'bool','valid'=>'','description'=>'是否是自营','example'=>'','default'=>''],
            'use_platform' => ['type'=>'string','valid'=>'','description'=>'商品使用平台(0=全部支持,1=仅支持pc端,2=仅支持wap端)如果查询不限制平台，则不需要传入该参数','example'=>'1','default'=>'0'],
            'min_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最小价格','example'=>'','default'=>''],
            'max_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最大价格','example'=>'','default'=>''],
            'bn' => ['type'=>'string','valid'=>'','description'=>'搜索商品货号','example'=>'','default'=>''],

            'approve_status' => ['type'=>'string','valid'=>'','description'=>'商品上架状态','example'=>'','default'=>''],
            'page_no' => ['type'=>'int','valid'=>'numeric','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'numeric','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
            'orderBy' => ['type'=>'string','valid'=>'','description'=>'排序方式','example'=>'','default'=>'modified_time desc,list_time desc'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的商品字段集','example'=>'','default'=>''],
        );
        $return['extendsFields'] = ['promotion','store'];
        return $return;
    }

    private function __getFilter($params)
    {
        $filterCols = ['item_id','shop_id','shop_cat_id','cat_id','search_keywords','approve_status','brand_id','prop_index','is_selfshop','dlytmpl_id'];
        foreach( $filterCols as $col )
        {
            if( $params[$col] )
            {
                $params[$col] = trim($params[$col]);

                if( in_array($col,['item_id','brand_id','shop_cat_id','prop_index']) )
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

        //获取指定商铺类目下的所有类目
        if($params['search_shop_cat_id'] >0 && $params['shop_id']){
            $catParams = array();
            $catParams['shop_id'] = $params['shop_id'];
            $catParams['parent_id'] = $params['search_shop_cat_id'];
            $catParams['fields'] = 'cat_id';
            $catIds = app::get('sysshop')->rpcCall('shop.cat.get', $catParams);
            $catIds = array_column($catIds, 'cat_id');
            $catIds[] = (int)$params['search_shop_cat_id'];
            $catIds = array_unique($catIds);
            $shopCatIds = array();
            foreach ($catIds as $v){
                $shopCatIds[] = ','. $v .',';
            }
            $filter['shop_cat_id'] = $shopCatIds;
        }
        if(isset($params['use_platform']) && $params['use_platform'] != null )
        {
            $filter['use_platform'] = explode(',',$params['use_platform']);
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

        if( $filter['prop_index'] )
        {
            foreach( (array)$filter['prop_index'] as $key=>$row )
            {
                $val = explode('_', $row);
                $propIndex[$val[0]][] = $val[1];
            }
            $filter['prop_index'] = $propIndex;
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
        $objMdlItem = app::get('sysitem')->model('item');

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
            $orderBy = "sold_quantity desc, item_id desc";
        }

        $data = kernel::single('search_object')->instance('item')
            ->page($page, $limit)
            ->buildExcerpts($params['buildExcerpts'], 'title')
            ->orderBy($orderBy)
            ->search($row,$this->__getFilter($params));

        $itemIds = array_column($data['list'], 'item_id');
        if( $itemIds && $params['fields']['extends']['store'] )
        {
            $itemStore = kernel::single('sysitem_item_info')->getItemStore($itemIds);
        }

        if( $itemIds && $params['fields']['extends']['promotion'] )
        {
            $promotionTag = app::get('sysitem')->model('item_promotion')->getList('*',array('item_id'=>$itemIds));
            $promotionArr = array();
            foreach ($promotionTag as $key => $v)
            {
                $promotionArr[$v['item_id']][$v['promotion_id']] = $v;
            }

            $giftFilter = array('item_id'=>implode(',',$itemIds));
            $giftData = app::get('sysitem')->rpcCall('promotion.gift.item.get',$giftFilter);
            foreach ($giftData as $key => $v)
            {
                $giftArr[$v['item_id']][$v['gift_id']] = $v;
            }

        }

        if( $itemStore || $promotionTag || $giftData)
        {
            foreach ($data['list'] as $key => &$value)
            {
                if( $itemStore )
                {
                    $value['store'] = $itemStore[$value['item_id']]['store'];
                    $value['freez'] = $itemStore[$value['item_id']]['freez'];
                }

                if( $promotionTag )
                {
                    $value['promotion'] = $promotionArr[$value['item_id']];
                }

                if($giftData)
                {
                    $value['gift'] = $giftArr[$value['item_id']];
                }
            }
        }

        return $data;
    }
}

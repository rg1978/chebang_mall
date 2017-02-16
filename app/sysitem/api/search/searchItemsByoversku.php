<?php
/**
 * searchItemsByoversku.php
 * Created Time 2016年3月29日 下午3:40:46
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysitem_api_search_searchItemsByoversku{

    public $apiDescription = '根据搜索条件和库存报警数获取商品列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
                'store' => ['type'=>'int','valid'=>'required','description'=>'店铺设置的库存警告数','example'=>'2','default'=>''],
                'shop_id' => ['type'=>'int','valid'=>'required','description'=>'店铺id','example'=>'','default'=>''],
                'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的商品字段集 item_id','example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
                'page_no' => ['type'=>'int','valid'=>'numeric','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
                'page_size' =>['type'=>'int','valid'=>'numeric','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
                'search_shop_cat_id' => ['type'=>'int','valid'=>'','description'=>'店铺搜索自有一级类目id','example'=>'','default'=>''],
                'search_keywords' => ['type'=>'string','valid'=>'','description'=>'搜索商品关键字','example'=>'','default'=>''],
                'use_platform' => ['type'=>'string','valid'=>'','description'=>'商品使用平台(0=全部支持,1=仅支持pc端,2=仅支持wap端)如果查询不限制平台，则不需要传入该参数','example'=>'1','default'=>'0'],
                'min_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最小价格','example'=>'','default'=>''],
                'max_price' => ['type'=>'int','valid'=>'numeric','description'=>'搜索最大价格','example'=>'','default'=>''],
                'bn' => ['type'=>'string','valid'=>'','description'=>'搜索商品货号','example'=>'','default'=>''],
        );
        return $return;
    }

    public function getItemList($params)
    {
        $objLibStore = kernel::single('sysitem_search_store');
        
        $itemCount = $objLibStore->getItemCountByStore($this->__getFilter($params));
        if(! $itemCount)
        {
            return [];
        }
        
        // 分页使用
        $pageTotal = ceil($itemCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 40;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $start = ($currentPage-1) * $limit;
        $itemList = $objLibStore->getItemListByStore($this->__getFilter($params), $start, $limit);
        $data['list'] = $itemList;
        $data['total_found'] = $itemCount;

        return $data;
    }

    private function __getFilter($params)
    {
        $filter = array();
        if ($params ['store'])
        {
            $filter['store'] = $params ['store'];
        }
        if ($params ['shop_id'])
        {
            $filter['shop_id'] = $params ['shop_id'];
        }
        if ($params ['search_keywords'])
        {
            $filter['search_keywords'] = $params ['search_keywords'];
        }

        if ($params ['min_price'])
        {
            $filter['min_price'] = $params ['min_price'];
        }

        if ($params ['max_price'])
        {
            $filter['max_price'] = $params ['max_price'];
        }

        if ($params ['use_platform'])
        {
            $filter['use_platform'] = $params ['use_platform'];
        }

        if ($params ['bn'])
        {
            $filter['bn'] = $params ['bn'];
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

        return $filter;
    }
}

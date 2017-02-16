<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商品搜索，调用搜索API后组织数据返回给控制器
 */
class topapi_item_search {

    /**
     * 每页搜索10个商品
     */
    public $limit = 10;

    /**
     * 搜索的最大页数，默认最大到100页
     */
    protected $_totalResults = 100;

    /**
     * 当前搜索商品的条件
     */
    private $activeFilter = null;

    /**
     * 当前搜索商品的ID集合
     */
    private $_itemIds = [];

    /**
     * 当前搜索商品的数据结构
     */
    private $_itemsList = [];

    /**
     * 设置每页搜索的商品
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 获取每页搜索的商品数据
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * 设置当前搜索的条件
     *
     */
    private function __setActiveFilter($params)
    {
        $this->activeFilter = $params;
        return $this;
    }

    /**
     * 获取当前搜索的条件
     */
    public function getActiveFilter()
    {
        return $this->activeFilter;
    }

    /**
     * 根据搜索条件搜索出商品基础数据
     *
     * @poaram array $filter 搜索的条件
     */
    public function search($filter)
    {
        //过滤搜索条件
        $objLibFilter = kernel::single('topapi_item_filter');
        $params = $objLibFilter->decode($filter);

        if( empty($params['cat_id'])
            && empty($params['search_keywords'])
            && empty($params['item_id'])
            && empty($params['shop_id'])
        )
        {
            $this->_itemsList['list'] = [];
            $this->_itemsList['total_found'] = 0;
            return $this;
        }

        $this->__setActiveFilter($params);

        $itemsList = app::get('topapi')->rpcCall('item.search',$this->__preSearchFilter($params));
        $this->__setTotalResults($itemsList['total_found']);
        if( !$itemsList['list'] )
        {
            $this->_itemsList = $itemsList;
            return $this;
        }

        $this->_itemsList['list'] = $itemsList['list'];//array_bind_key($itemsList['list'],'item_id');
        $this->_itemIds = array_column($itemsList['list'],'item_id');

        return $this;
    }

    public function getData()
    {
        return $this->_itemsList;
    }

    public function getId()
    {
        return $this->_itemIds;
    }

    /**
     * 给搜索出的商品打上平台活动标签
     *
     * @param $itemIds array 搜索出的商品ID集合
     * @param $itemsList array 搜索出的商品，需要商品ID作为key
     */
    public function setItemsActivetyTag($itemIds=null, $itemsList=null)
    {
        if( !$itemIds )   $itemIds   = $this->_itemIds;
        if( !$itemsList ) $itemsList = $this->_itemsList;

        if( empty($itemIds) || empty($itemsList['list']) )
        {
            $this->_itemsList = $itemsList;
            return $this;
        }

        $activityParams['item_id'] = implode(',',$itemIds);
        $activityParams['status'] = 'agree';
        $activityParams['end_time'] = 'bthan';
        $activityParams['start_time'] = 'sthan';
        $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
        $activityItemList = app::get('topapi')->rpcCall('promotion.activity.item.list',$activityParams);
        if( !$activityItemList )
        {
            $this->_itemsList = $itemsList;
            return $this;
        }
        $activityItemList = array_bind_key($activityItemList['list'],'item_id');

        foreach($itemsList['list'] as &$v)
        {
            if($activityItemList[$v['item_id']])
            {
                $v['activity'] = $activityItemList[$v['item_id']];
            }
        }

        $this->_itemsList = $itemsList;
        return $this;
    }

    /**
     * 给商品打上促销标签
     *
     * @param $itemsList array 搜索出的商品
     */
    public function setItemsPromotionTag($itemsList=null)
    {
        if( !$itemsList ) $itemsList = $this->_itemsList;
        if( empty($itemsList['list']) )
        {
            $this->_itemsList = $itemsList;
            return $this;
        }

        foreach($itemsList['list'] as &$row )
        {
            if( $row['promotion'] )
            {
                $promotionIds = $promotionIds ? array_merge(array_column($row['promotion'], 'promotion_id'), $promotionIds) : array_column($row['promotion'], 'promotion_id');
            }
            else
            {
                $row['promotion'] = [];
            }
        }

        if( !$promotionIds )
        {
            $this->_itemsList = $itemsList;
            return $this;
        }

        $promotionIds = array_unique($promotionIds);
        $promotionTags = app::get('topapi')->rpcCall('promotion.promotion.list.tag', ['promotion_id'=>implode(',', $promotionIds),'platform'=>'wap']);
        if( !$promotionTags )
        {
            $this->_itemsList = $itemsList;
            return $this;
        }

        foreach( $itemsList['list'] as $key=>&$val )
        {
            if( !$val['promotion'] )
            {
                $val['promotion'] = [];
                continue;
            }
            $promotions = [];
            foreach( $val['promotion'] as $promotionRow )
            {
                if($promotionTags[$promotionRow['promotion_id']])
                {
                    $promotions[] = [
                        'promotion_id' => $promotionRow['promotion_id'],
                        'tag' => $promotionTags[$promotionRow['promotion_id']]['tag']
                    ];
                }
            }
            if($promotions)
            {
                $val['promotion'] = $promotions;
            }
            else
            {
                $val['promotion'] = [];
            }
        }

        $this->_itemsList = $itemsList;
        return $this;
    }

    /**
     * 设置搜索出商品的页数，用于分页搜索判断
     *
     * @param $totalItem int 搜索出的商品
     */
    private function __setTotalResults($totalItem)
    {
        if( $totalItem > 0 )
        {
            $this->_totalResults = $totalItem;
        }
        else
        {
            $this->_totalResults = 0;
        }

        return $this;
    }

    /**
     * 返回搜索商品页数
     */
    public function getTotalResults()
    {
        return $this->_totalResults;
    }

    /**
     * 组织搜索商品API的条件
     */
    private function __preSearchFilter($params)
    {
        $searchParams = $params;
        $searchParams['page_no'] = ($params['page_no'] >=1) ? $params['page_no'] : 1;
        $searchParams['page_size'] = ($params['page_size'] >=1) ? $params['page_size'] : $this->limit;

        $searchParams['approve_status'] = 'onsale'; //搜索必须为上架的商品
        $searchParams['use_platform'] = '0,2';//搜索显示在手机端的商品
        $searchParams['buildExcerpts'] = false;//是否需要进行高亮显示商品名称
        $searchParams['fields'] = 'item_id,title,image_default_id,price,sold_quantity,promotion';//需要返回的商品字段
        return $searchParams;
    }
}


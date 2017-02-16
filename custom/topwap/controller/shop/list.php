<?php
/**
 * 店铺搜索控制器
 */
class topwap_ctl_shop_list extends topwap_ctl_shop {

    public function __construct()
    {
        parent::__construct();
        $this->objLibSearch = kernel::single('topwap_item_search');
    }

    public function index()
    {
        $filter = input::get();

         //标签获取
        if($filter['widgets_id']&&$filter['widgets_type'])
        {
            $tagInfo = shopWidgets::getWapInfo($filter['widgets_type'],$filter['shop_id'],$filter['widgets_id']);
            foreach ($tagInfo[0]['params']['item_id'] as $key => $value)
            {
                $item_id .= $value.',';
            }
            $filter['item_id'] = rtrim($item_id, ",");
            unset($filter['widgets_id'],$filter['widgets_type']);
        }

        $itemsList = $this->objLibSearch->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        $pagedata['items'] = $itemsList['list'];

        $activeFilter = $this->objLibSearch->getActiveFilter();
        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['search_keywords'] = $activeFilter['search_keywords'];
        $pagedata['shopId'] = $activeFilter['shop_id'];

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        //店铺分类
        $pagedata['shopcat'] = app::get('topwap')->rpcCall('shop.cat.get',array('shop_id'=>$activeFilter['shop_id']));
        foreach($pagedata['shopcat'] as $shopCatId=>&$row)
        {
            if( $row['children'] )
            {
                $row['cat_id'] = $row['cat_id'].','.implode(',', array_column($row['children'], 'cat_id'));
            }
        }
        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();
        return $this->page('topwap/shop/list/index.html', $pagedata);
    }

    public function newgoods()
    {
        $filter = input::get();
       

         //标签获取
        if($filter['widgets_id']&&$filter['widgets_type'])
        {
            $tagInfo = shopWidgets::getWapInfo($filter['widgets_type'],$filter['shop_id'],$filter['widgets_id']);
            foreach ($tagInfo[0]['params']['item_id'] as $key => $value)
            {
                $item_id .= $value.',';
            }
            $filter['item_id'] = rtrim($item_id, ",");
            unset($filter['widgets_id'],$filter['widgets_type']);
        }

        $itemsList = $this->objLibSearch->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        $pagedata['items'] = $itemsList['list'];

        $activeFilter = $this->objLibSearch->getActiveFilter();
        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['search_keywords'] = $activeFilter['search_keywords'];
        $pagedata['shopId'] = $activeFilter['shop_id'];

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        //店铺分类
        $pagedata['shopcat'] = app::get('topwap')->rpcCall('shop.cat.get',array('shop_id'=>$activeFilter['shop_id']));
        foreach($pagedata['shopcat'] as $shopCatId=>&$row)
        {
            if( $row['children'] )
            {
                $row['cat_id'] = $row['cat_id'].','.implode(',', array_column($row['children'], 'cat_id'));
            }
        }
        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();

        return $this->page('topwap/shop/list/new.html', $pagedata);
    }

    public function ajaxGetItemList()
    {
        $filter = input::get();
        $itemsList = $this->objLibSearch->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        $pagedata['items'] = $itemsList['list'];

        $activeFilter = $this->objLibSearch->getActiveFilter();
        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        return view::make('topwap/shop/list/item_list.html',$pagedata);
    }
}


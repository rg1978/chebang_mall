<?php
/**
 * 商品列表页控制器
 */
class topwap_ctl_item_list extends topwap_controller {


    public function __construct()
    {
        $this->objLibSearch = kernel::single('topwap_item_search');
    }

    public function index()
    {
        $filter = input::get();
        $itemsList = $this->objLibSearch->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        $pagedata['items'] = $itemsList['list'];

        $activeFilter = $this->objLibSearch->getActiveFilter();
        $pagedata['activeFilter'] = $activeFilter;
        $pagedata['search_keywords'] = $activeFilter['search_keywords'];

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['pagers']['total'] = $this->objLibSearch->getMaxPages();
        $pagedata['screen'] = $this->__itemListFilter($filter);

        return $this->page('topwap/item/list/index.html', $pagedata);
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

        if( !$pagedata['pagers']['total'] )
        {
            return view::make('topwap/empty/item.html',$pagedata);
        }

        if($pagedata['items'])
        {
            return view::make('topwap/item/list/item_list.html',$pagedata);
        }
    }
    
    // 商品搜索
    private function __itemListFilter($postdata)
    {
        $objLibFilter = kernel::single('topwap_item_filter');
        $params = $objLibFilter->decode($postdata);
        $params['use_platform'] = '0,2';
        $filterItems = app::get('topwap')->rpcCall('item.search.filterItems',$params);
        if($filterItems['shopInfo'])
        {
            $wapslider = shopWidgets::getWapInfo('waplogo',$filterItems['shopInfo']['shop_id']);
            $filterItems['logo_image'] = $wapslider[0]['params'];
        }
        
        //渐进式筛选的数据
        return $filterItems;
    }
    
}


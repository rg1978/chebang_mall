<?php
/**
 * topapi
 *
 * -- item.search
 * -- 会员中心首页数据统计
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_item_itemSearch implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取商品列表';

    public function __construct()
    {
        $this->objLibSearch = kernel::single('topapi_item_search');
    }

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        $return = array(
            // 'item_id' => ['type'=>'string','valid'=>'','example'=>'2,3,5,6','desc'=>'商品id，多个id用，隔开','msg'=>''],
            // 'shop_id' => ['type'=>'int','valid'=>'integer','example'=>'','desc'=>'店铺id','msg'=>''],
            // 'dlytmpl_id' => ['type'=>'int','valid'=>'integer','example'=>'','desc'=>'运费模板id','msg'=>''],
            'shop_cat_id' => ['type'=>'int','valid'=>'string','example'=>'1','desc'=>'店铺自定义商品分类id','msg'=>'店铺分类id必须是正整数'],
            // 'search_shop_cat_id' => ['type'=>'int','valid'=>'','example'=>'','desc'=>'店铺搜索自有一级类目id','msg'=>''],
            'cat_id' => ['type'=>'int','valid'=>'','example'=>'1','desc'=>'平台的商品类目id','msg'=>'平台类目id必须是正整数'],
            'brand_id' => ['type'=>'int','valid'=>'','example'=>'1','desc'=>'平台的品牌id','msg'=>'平台品牌id必须是正整数'],
            // 'prop_index' => ['type'=>'string','valid'=>'','example'=>'','desc'=>'商品自然属性','msg'=>''],
            'search_keywords' => ['type'=>'string','valid'=>'','example'=>'iphone','desc'=>'商品相关关键字','msg'=>''],
            // 'buildExcerpts' => ['type'=>'bool','valid'=>'','example'=>'','desc'=>'是否关键字高亮','msg'=>''],
            'is_selfshop' => ['type'=>'bool','valid'=>'','example'=>'1','desc'=>'是否是自营','msg'=>''],
            // 'use_platform' => ['type'=>'string','valid'=>'','example'=>'1','desc'=>'商品使用平台(0=全部支持,1=仅支持pc端,2=仅支持wap端)如果查询不限制平台，则不需要传入该参数','msg'=>''],
            // 'min_price' => ['type'=>'int','valid'=>'numeric',example'=>'','desc'=>'搜索最小价格','msg'=>''],
            // 'max_price' => ['type'=>'int','valid'=>'numeric',example'=>'','desc'=>'搜索最大价格','msg'=>''],
            // 'bn' => ['type'=>'string','valid'=>'','example'=>'','desc'=>'搜索商品货号','msg'=>''],

            // 'approve_status' => ['type'=>'string','valid'=>'','example'=>'','desc'=>'商品上架状态','msg'=>''],
            'page_no' => ['type'=>'int','valid'=>'numeric','example'=>'1','desc'=>'分页当前页码,1<=no<=499','msg'=>''],
            'page_size' =>['type'=>'int','valid'=>'numeric','example'=>'10','desc'=>'分页每页条数(1<=size<=200)','msg'=>''],
            'orderBy' => ['type'=>'string','valid'=>'','example'=>'price','example'=>'modified_time desc,list_time desc','desc'=>'排序方式.商品的主要关键字排序','msg'=>''],
            'fields' => ['type'=>'field_list','valid'=>'','example'=>'title,price','desc'=>'要获取的商品字段集'],
        );
        // $return['extendsFields'] = ['promotion','store'];
        return $return;
    }

    /**
     * @return 
     */
    public function handle($params)
    {
        $filter = $params;
        $filter['page_no'] = $params['page_no'] ? : 1;
        $limit = $params['page_size'] ? : 10;
        $itemsList = $this->objLibSearch->setLimit($limit)
                          ->search($filter)
                          ->setItemsActivetyTag()
                          ->setItemsPromotionTag()
                          ->getData();

        $pagedata['list'] = $itemsList['list'];

        // $activeFilter = $this->objLibSearch->getActiveFilter();
        // $pagedata['activeFilter'] = $activeFilter;
        // $pagedata['search_keywords'] = $activeFilter['search_keywords'];

        //默认图片
        // $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $pagedata['pagers']['total'] = $this->objLibSearch->getTotalResults();


        $pagedata['cur_symbol'] = app::get('topapi')->rpcCall('currency.get.symbol',array());
        // $pagedata['screen'] = $this->__itemListFilter($filter);
        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"item_id":130,"title":"etam艾格时尚修身连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/5d/7c/23/aa461ffb145e94922c1036e5aa8edbbb138f1bf6.png","price":"149.000","sold_quantity":0,"promotion":[]},{"item_id":128,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png","price":"224.000","sold_quantity":9,"promotion":[]},{"item_id":122,"title":"夏季新品欧美风圆领无袖女式网眼镂空雪纺连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/48/a6/10/eafdb9c20e9607486befbfac23fcffa16a8a5c35.png","price":"499.000","sold_quantity":1,"promotion":[]},{"item_id":123,"title":"拼接微透长袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/6d/6e/23/5d1dede8b5575c7f41ee6b865278b437ad9f3173.png","price":"654.000","sold_quantity":3,"promotion":[]},{"item_id":26,"title":"ONLY春季新品条纹彼得潘领包臀五分袖连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/74/32/fc/414d7236e416eb52ebd17b2e30f8d1f0fb85838c.jpg","price":"249.000","sold_quantity":1,"promotion":[]},{"item_id":23,"title":"ONLY春季新品修身包臀可拆卸衣摆连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/e3/db/b2/84e4fceb2da83b49091965d7bdcc7f6265fb2dc6.jpg","price":"599.000","sold_quantity":7,"promotion":[]},{"item_id":24,"title":"ONLY秋装新品厚实针织显瘦七分袖修身连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/a3/18/9d/d1897d3766862b7d5515b0c2aa4eea06042d98fb.jpg","price":"299.000","sold_quantity":4,"promotion":[]},{"item_id":25,"title":"ONLY秋装新品纯棉宽松钉珠装饰针织连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/eb/c4/5b/e2362bad2de598a00cee337d8ed19008c94d189f.jpg","price":"249.000","sold_quantity":0,"promotion":[]},{"item_id":22,"title":"ONLY冬装新品宽松圆领底摆开叉设计针织连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/29/e5/22/670cf312b0aaace1ebf6305d6f346ee147f29c16.jpg","price":"299.000","sold_quantity":1,"promotion":[]},{"item_id":80,"title":"FOREVER21 小高领修身长袖针织连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/56/30/61/070068e4659bb8c958b960fa6bd487bfd42903a3.png","price":"149.000","sold_quantity":1,"promotion":[{"promotion_id":4,"tag":"免邮"}]}],"pagers":{"total":12},"cur_symbol":{"sign":"￥","decimals":2}}}';
    }

    // 商品搜索
    private function __itemListFilter($postdata)
    {
        $objLibFilter = kernel::single('topapi_item_filter');
        $params = $objLibFilter->decode($postdata);
        $params['use_platform'] = '0,2';
        $filterItems = app::get('topapi')->rpcCall('item.search.filterItems',$params);
        if($filterItems['shopInfo'])
        {
            $wapslider = shopWidgets::getWapInfo('waplogo', $filterItems['shopInfo']['shop_id']);
            $filterItems['logo_image'] = $wapslider[0]['params'];
        }
        
        //渐进式筛选的数据
        return $filterItems;
    }

}

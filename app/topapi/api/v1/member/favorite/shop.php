<?php
/**
 * topapi
 *
 * -- member.favorite.shop.list
 * -- 获取我的店铺收藏列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_favorite_shop implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取我的店铺收藏列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'page_no'   => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'每页数据条数,默认20条'],
            'orderBy'   => ['type'=>'string', 'valid'=>'',  'example'=>'', 'desc'=>'排序'],
        ];
    }

    public function handle($params)
    {
        $shopFavorite = app::get('topapi')->rpcCall('user.shopcollect.list', $params);
        $data = array();
        if( $shopFavorite )
        {
            foreach( $data['list'] as &$row )
            {
                $row['shop_logo'] = base_storager::modifier($row['shop_logo'], 't');
            }
            $data['list'] = $shopFavorite['shopcollect'];
            $data['pagers']['total'] = $shopFavorite['shopcount'];
        }
        return $data;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"snotify_id":6,"shop_id":4,"user_id":4,"shop_name":"onex生鲜","shop_logo":"http://images.bbc.shopex123.com/images/bb/2a/51/85840720e0cc9652f2b7f1fa6209859456dbd8c4.png","create_time":1471857280}],"pagers":{"total":3}}}';
    }
}


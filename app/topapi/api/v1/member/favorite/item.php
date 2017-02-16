<?php
/**
 * topapi
 *
 * -- member.favorite.item.list
 * -- 获取我的商品收藏列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_favorite_item implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取我的商品收藏列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'page_no'   => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'每页数据条数,默认10条'],
            'orderBy'   => ['type'=>'string', 'valid'=>'',  'example'=>'', 'desc'=>'排序'],
        ];
    }

    public function handle($params)
    {
        $favData = app::get('topapi')->rpcCall('user.itemcollect.list',$params);
        $data['list'] = [];
        if( $favData['itemcollect'] )
        {
            $defaultImageId = kernel::single('image_data_image')->getImageSetting('item');
            foreach( $favData['itemcollect'] as $value )
            {
                $value['image_default_id'] = $value['image_default_id'] ? base_storager::modifier($value['image_default_id'], 't') : base_storager::modifier($defaultImageId['t']['default_image']);
                unset($value['sku_id']);
                $data['list'][] = $value;
            }
        }

        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $data['cur_symbol'] = $cur_symbol;

        $data['pagers']['total'] = $favData['itemcount'];
        return $data;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"gnotify_id":14,"item_id":137,"user_id":4,"cat_id":33,"goods_name":"sphinx1","goods_price":"100.000","image_default_id":"","email":null,"cellphone":null,"send_time":null,"create_time":1471857131,"disabled":0,"remark":null,"object_type":"goods"}],"pagers":{"total":3}}}';
    }
}


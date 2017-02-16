<?php
/**
 * topapi
 *
 * -- member.aftersales.list
 * -- 会员退换货记录列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_aftersales_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员退换货记录列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'page_no'   => ['type'=>'int', 'valid'=>'numeric', 'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'numeric', 'example'=>'', 'desc'=>'每页数据条数,默认20条'],
            'fields' => ['type'=>'field_list', 'valid'=>'', 'example'=>'', 'desc'=>'需要返回的数据'],
        );

        return $return;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"shop_id":3,"aftersales_bn":1608241420310304,"aftersales_type":"REFUND_GOODS","created_time":1472019618,"oid":1608041129430004,"tid":1608041129420004,"num":1,"progress":"3","status":"3","sku":{"oid":1608041129430004,"sku_id":22,"bn":"S56A5C166E0B0C","item_id":22,"title":"ONLY冬装新品宽松圆领底摆开叉设计针织连衣裙女","pic_path":"http://images.bbc.shopex123.com/images/29/e5/22/670cf312b0aaace1ebf6305d6f346ee147f29c16.jpg_t.jpg","spec_nature_info":"颜色：白色、尺码：s","price":"299.000","payment":"299.000","aftersales_status":"SELLER_REFUSE_BUYER","complaints_status":"BUYER_CLOSED","points_fee":"0.000","consume_point_fee":0},"shopname":"onexbbc自营店（自营店铺）自营店","aftersales_type_desc":"退货退款","status_desc":"已驳回","is_complaints":false}],"cur_symbol":{"sign":"￥","decimals":2},"pagers":{"total":3}}}';
    }

    public function handle($params)
    {
        if( ! $params['fields'] || $params['fields'] == '*' )
        {
            $params['fields'] = 'shop_id,aftersales_bn,aftersales_type,created_time,oid,tid,num,progress,status,sku';
        }

        $result = app::get('topapi')->rpcCall('aftersales.list.get', $params);
        $shopIds = array_column($result['list'],'shop_id');
        if( $shopIds )
        {
            $shopIds = array_unique($shopIds);
            $shopData = app::get('topapi')->rpcCall('shop.get.list', ['shop_id'=>implode(',',$shopIds),'fields'=>'shop_id,shop_name']);
        }
        foreach( $shopData as $shopRow )
        {
            $shopname[$shopRow['shop_id']] = $shopRow['shopname'];
        }

        $defaultImageId = kernel::single('image_data_image')->getImageSetting('item');

        foreach( $result['list'] as $key=>&$row )
        {
            $row['shopname'] = $shopname[$row['shop_id']];

            $row['aftersales_type_desc'] = $this->__aftersalesType[$row['aftersales_type']];
            $row['status_desc'] = $this->__status[$row['status']];

            //判断该售后是否可以进行投诉
            if( $row['progress'] == 3 && $row['sku']['complaints_status'] == 'NOT_COMPLAINTS' )
            {
                $row['is_complaints'] = true;
            }
            else
            {
                $row['is_complaints'] = false;
            }

            //处理商品图片
            if( $row['sku']['pic_path'] )
            {
                $row['sku']['pic_path'] = base_storager::modifier($row['sku']['pic_path'], 't');
            }
            else
            {
                $row['sku']['pic_path'] = base_storager::modifier($defaultImageId['t']['default_image']);
            }
        }

        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $result['cur_symbol'] = $cur_symbol;

        $result['pagers']['total'] = $result['total_found'];
        unset($result['total_found']);
        return $result;
    }

    private $__aftersalesType = [
        'ONLY_REFUND' => '仅退款',
        'REFUND_GOODS' => '退货退款',
        'EXCHANGING_GOODS' => '换货',
    ];

    private $__status = [
        '0' => '待处理',
        '1' => '处理中',
        '2' => '已处理',
        '3' => '已驳回',
    ];
}

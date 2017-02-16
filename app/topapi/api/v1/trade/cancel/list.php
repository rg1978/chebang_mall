<?php
/**
 * topapi
 *
 * -- trade.cancel.list
 * -- 获取会员取消订单列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_cancel_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取会员取消订单列表';

    /**
     * 定义API传入的应用级参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'page_no'   => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'numeric',  'example'=>'', 'desc'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'string', 'valid'=>'',  'example'=>'', 'desc'=>'需返回的字段'],
        );

        return $return;
    }

    public function handle($params)
    {
        $params['page_no'] = intval($params['page_no']) ? intval($params['page_no']) : 1;
        $params['page_size'] = intval($params['page_size']) ? intval($params['page_size']) : 10;

        if( empty($params['fields']) || $params['fields'] == '*' )
        {
            $params['fields'] = "cancel_id,shop_id,payed_fee,refunds_status,tid";
        }

        $params['order_by'] = 'created_time desc';

        $data = app::get('topwap')->rpcCall('trade.cancel.list.get', $params);
        foreach( $data['list'] as $row)
        {
            foreach( $row['order'] as &$orderRow )
            {
                $orderRow['pic_path'] = base_storager::modifier($orderRow['pic_path'], 't');
            }
            $return['list'][] = $row;
        }

        $return['pagers']['total'] = $data['total'];

        return $return;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"cancel_id":23,"shop_id":1,"payed_fee":"219.000","refunds_status":"SUCCESS","tid":1609061046430004,"order":[{"tid":1609061046430004,"oid":1609061046440004,"title":"Gap含羊毛清新雪花织纹圆领毛衣|男装721348","pic_path":"http://images.bbc.shopex123.com/images/d0/66/82/ea8582455c733dee57055d741345ac8ad050b73f.png","item_id":38,"sku_id":122,"spec_nature_info":"颜色：白色、尺码：s","num":1,"gift_data":null}]}],"pagers":{"total":8}}}';
    }
}

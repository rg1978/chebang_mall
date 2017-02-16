<?php
/**
 * topapi
 *
 * -- member.aftersales.get
 * -- 会员退换货记录详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_aftersales_get implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员退换货记录详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'aftersales_bn' => ['type'=>'string', 'valid'=>'required|numeric', 'example'=>'', 'desc'=>'售后编码'],
            'fields' => ['type'=>'field_list', 'valid'=>'', 'example'=>'', 'desc'=>'需要返回的数据'],
        );

        return $return;
    }

    public function handle($params)
    {
        if( ! $params['fields'] || $params['fields'] == '*' )
        {
            $params['fields'] = 'aftersales_bn,created_time,status,modified_time,tid,aftersales_bn,aftersales_type,reason,description,evidence_pic,shop_explanation,progress,sendback_data,sendconfirm_data';
        }

        $result = app::get('topapi')->rpcCall('aftersales.get', $params);
        if( empty($result) ) return [];

        $result['status_desc'] = $this->__status[$result['status']];
        $result['aftersales_type_desc'] = $this->__aftersalesType[$result['aftersales_type']];

        //处理商品图片
        if( $result['sku']['pic_path'] )
        {
            $result['sku']['pic_path'] = base_storager::modifier($result['sku']['pic_path'], 't');
        }
        else
        {
            $defaultImageId = kernel::single('image_data_image')->getImageSetting('item');
            if( isset($result['sku']['pic_path']) )
            {
                $result['sku']['pic_path'] = base_storager::modifier($defaultImageId['t']['default_image']);
            }
        }

        //处理售后凭证图片
        if( $result['evidence_pic'] )
        {
            $result['evidence_pic'] = $result['evidence_pic'] ? explode(',',$result['evidence_pic']) : '';
            foreach( $result['evidence_pic'] as &$imageUrl)
            {
                $imageUrl = base_storager::modifier($imageUrl);
            }
        }

        //判断消费者是否需要回寄商品
        if( $result['aftersales_type'] != 'ONLY_REFUND' && $result['progress'] === '1' )
        {
            $result['is_return_goods'] = true;
        }
        else
        {
            $result['is_return_goods'] = false;
        }

        //判断是否需要显示消费者回寄物流信息
        if( $result['aftersales_type'] != 'ONLY_REFUND' && in_array($result['progress'], ['2','4','5','8','6','7']) )
        {
            $result['is_show_return_goods'] = true;
        }
        else
        {
            $result['is_show_return_goods'] = false;
        }

        //判断是否需要显示商家寄送的物流信息
        if( $result['aftersales_type'] == 'EXCHANGING_GOODS' && in_array($result['progress'], ['4','8']) )
        {
            $result['show_shop_return_goods'] = true;
        }
        else
        {
            $result['show_shop_return_goods'] = false;
        }

        if( $result['sendback_data'] )
        {
            $result['sendback_data'] = $result['sendback_data'] ? unserialize($result['sendback_data']) : '';
            unset($result['returnGoodsLogistics']);
        }

        if( isset($result['sendconfirm_data']) )
        {
            $result['sendconfirm_data'] = $result['sendconfirm_data'] ? unserialize($result['sendconfirm_data']) : '';
        }

        return $result;
    }

    private $__status = [
        '0' => '待处理',
        '1' => '处理中',
        '2' => '已处理',
        '3' => '已驳回',
    ];

    private $__aftersalesType = [
        'ONLY_REFUND' => '仅退款',
        'REFUND_GOODS' => '退货退款',
        'EXCHANGING_GOODS' => '换货',
    ];

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"aftersales_bn":1608211943230304,"aftersales_type":"REFUND_GOODS","modified_time":1471851660,"reason":"质量原因","sendback_data":{"corp_code":"SF-顺丰速递","logi_name":null,"logi_no":"135987463","receiver_address":"上海徐汇区桂林路396号2号楼","mobile":"13918765435"},"sendconfirm_data":null,"description":"dsefsf","shop_explanation":"同意","admin_explanation":null,"evidence_pic":null,"created_time":1471779823,"oid":1608041518230004,"tid":1608041518220004,"num":1,"progress":"8","status":"1","refunds":{"status":"3","total_price":"0.010","refund_fee":"0.010"},"sku":{"oid":1608041518230004,"sku_id":383,"bn":"G56A709D9D0E38","item_id":89,"title":"ONex-OMS订单管理系统","pic_path":"http://images.bbc.shopex123.com/images/69/4f/60/29dba7d78a4927fbba019f0bdd54ba363e59a913.png_s.png","spec_nature_info":null,"price":"0.010","payment":"0.010","aftersales_status":"REFUNDING","complaints_status":"NOT_COMPLAINTS","points_fee":"0.000","consume_point_fee":0,"refund_fee":"0.010"},"trade":{"status":"TRADE_FINISHED","shop_id":3,"receiver_name":"shopex","user_id":4,"receiver_state":"天津市","receiver_city":"和平区","receiver_district":null,"receiver_address":"3213","receiver_mobile":"13918087430","receiver_phone":null,"refund_fee":0.01}}}';
    }

}

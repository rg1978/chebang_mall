<?php

class sysaftersales_api_refundapply_shopCheck {

    /**
     * 接口作用说明
     */
    public $apiDescription = '商家审核退款申请单(OMS创建退款申请单后直接审核通过)';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'string','valid'=>'required', 'description'=>'店铺ID'],
            'refund_bn' => ['type'=>'string','valid'=>'required', 'description'=>'refund_bn退款申请单编码'],
            'status' => ['type'=>'string','valid'=>'required', 'description'=>'审核状态 agree 通过，reject 拒绝'],
            'reason' => ['type'=>'json','valid'=>'', 'description'=>'仅在审核不通过时填写该值,审核不通过原因'],
        );
        return $return;
    }

    public function reply($params)
    {
        $objMdlRefunds = app::get('sysaftersales')->model('refunds');
        $filter['refund_bn'] = $params['refund_bn'];
        $filter['shop_id'] = $params['shop_id'];

        $data = $objMdlRefunds->getRow('status,shop_id,tid,refunds_type,hongbao_fee,refund_fee,total_price,refunds_id',$filter);
        if( !$data )
        {
            throw new \LogicException(app::get('sysaftersales')->_('审核的退款申请单不存在'));
        }

        if( $data['status'] != '0' )
        {
            throw new \LogicException(app::get('sysaftersales')->_('该退款申请单已审核，不能重复审核'));
        }

        if( $data['refunds_type'] == '1')
        {
            $cancelData = app::get('systrade')->rpcCall('trade.cancel.list.get',['tid'=>$data['tid'],'shop_id'=>$params['shop_id'],'fields'=>'cancel_id,tid']);

            $apiParams['shop_id'] = $params['shop_id'];
            $apiParams['status'] = $params['status'];
            $apiParams['cancel_id'] = $cancelData['list'][$data['tid']]['cancel_id'];
            $apiParams['reason'] = $params['reason'];
            app::get('systrade')->rpcCall('trade.cancel.shop.check',$apiParams);

            if( $data['refund_fee'] == $data['hongbao_fee'] )
            {
                app::get('sysaftersales')->rpcCall('aftersales.refunds.restore', array('refunds_id'=>$data['refunds_id'], 'return_fee'=>$data['total_price']));
            }
        }

        return true;
    }
}


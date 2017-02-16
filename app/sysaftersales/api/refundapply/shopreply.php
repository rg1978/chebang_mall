<?php

class sysaftersales_api_refundapply_shopreply {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新退款申请单状态';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'string','valid'=>'required', 'description'=>'店铺ID'],
            'status' => ['type'=>'string','valid'=>'required', 'description'=>'退款申请单审核状态'],
            'refunds_id' => ['type'=>'string','valid'=>'', 'description'=>'退款申请单ID，如果有退款申请单ID则通过该参数进行审核'],
            'aftersales_bn' => ['type'=>'string','valid'=>'', 'description'=>'申请售后的编号，未填写退款申请单，则根据该售后单ID审核'],
            'tid' => ['type'=>'string','valid'=>'', 'description'=>'订单ID，如果未填写退款申请单ID和售后申请单ID，那么则根据订单ID审核取消订单的退款单'],
        );
        return $return;
    }

    public function reply($params)
    {
        if( $params['refunds_id'] )
        {
            $filter['refunds_id'] = $params['refunds_id'];
        }
        elseif($params['aftersales_bn'])
        {
            $filter['aftersales_bn'] = $params['aftersales_bn'];
        }
        elseif( $params['tid'] )
        {
            $filter['tid'] = $params['tid'];
            $filter['refunds_type'] = '1';
        }
        else
        {
            throw new \LogicException(app::get('sysaftersales')->_('参数错误'));
        }


        $objMdlRefunds = app::get('sysaftersales')->model('refunds');

        $status = $params['status'];

        $data = $objMdlRefunds->getRow('tid,oid,user_id,refund_bn,total_price,refunds_type,status,shop_id,refunds_id',$filter);
        if( $data['status'] != '0' )
        {
            throw new \LogicException(app::get('sysaftersales')->_('该退款申请已审核，不需要重新审核'));
        }

        $refundsfilter['shop_id'] = $params['shop_id'];
        $refundsfilter['refunds_id'] = $data['refunds_id'];
        $result = $objMdlRefunds->update(['status'=>$status,'modified_time'=>time()], $refundsfilter);

        event::fire('refund.modified', [$data]);

        return $data;
    }
}


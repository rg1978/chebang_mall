<?php

class sysaftersales_api_refundapply_createByShop {

    /**
     * 接口作用说明
     */
    public $apiDescription = '创建退款申请单（OMS发起）';

    public function getParams()
    {
        $return['params'] = array(
            'refund_bn' => ['type'=>'string','valid'=>'required', 'description'=>'退款申请单编号，如果未填写则自动生成'],
            'aftersales_bn' => ['type'=>'string','valid'=>'', 'description'=>'售后申请单（退款关联的售后申请单编号）'],
            'shop_id' => ['type'=>'string','valid'=>'required', 'description'=>'店铺ID'],
            'tid' => ['type'=>'string','valid'=>'required', 'description'=>'订单号'],
            'reason' => ['type'=>'json','valid'=>'', 'description'=>'申请退款理由'],
            'total_price' => ['type'=>'string','valid'=>'required', 'description'=>'申请退款的金额，取消订单不需要填写退款金额'],
        );
        return $return;
    }

    public function create($params)
    {
        $objMdlRefunds = app::get('sysaftersales')->model('refunds');
        $data = $objMdlRefunds->getRow('refunds_id,status',['refund_bn'=>$params['refund_bn']]);
        //有退款单，并且未审核
        if(  $data  )
        {
            if( $data['status'] == '0' )
            {
                $checkParams['shop_id'] = $params['shop_id'];
                $checkParams['refund_bn'] = $params['refund_bn'];
                $checkParams['status'] = 'agree';
                app::get('sysaftersales')->rpcCall('aftersales.refundapply.shop.check',$checkParams);
                return true;
            }
            else
            {
                throw new \Exception("已经存在相同编号的退款申请单，不需要重新创建");
            }
        }

        $tradeGetParams = ['tid' => $params['tid'], 'fields' =>'tid,payed_fee,user_id,shop_id,status,pay_type,points_fee'];
        $tradeData = app::get('sysaftersales')->rpcCall('trade.get', $tradeGetParams);
        if( $tradeData['shop_id'] != $params['shop_id'])
        {
            throw new \Exception("参数错误,没有权限对该订单进行退款");
        }

        if( $params['aftersales_bn'] && $params['aftersales_bn'] != 'null' )
        {
            $afterSalesRow = app::get('sysaftersales')->model('aftersales')->getRow('tid,oid',['aftersales_bn'=>$params['aftersales_bn']]);
            if( empty($afterSalesRow)  )
            {
                throw new \Exception("退款的售后单不存在");
            }

            $oid = $afterSalesRow['oid'];
            $orderData = app::get('sysaftersales')->rpcCall('trade.order.get',['oid'=>$oid,'fields'=>'points_fee']);

            $createData['shop_id'] = $params['shop_id'];
            $createData['aftersales_bn'] = $params['aftersales_bn'];
            $createData['tid'] = $params['tid'];
            $createData['refunds_type'] = '0';//售后退款
            $createData['total_price'] = ecmath::number_plus([$params['total_price'],$orderData['points_fee']]);
            $createData['reason'] = $params['reason'] ? $params['reason'] : '同意售后申请退款' ;
            $createData['status'] = '3';
            $createData['oid'] = $oid;
            $createData['refund_bn'] = $params['refund_bn'];
            app::get('sysaftersales')->rpcCall('aftersales.refundapply.create',$createData);
        }
        else
        {
            //如果是已付款为未发货，并且退款金额为全额退款
            if( $tradeData['status'] == 'WAIT_SELLER_SEND_GOODS' && ($tradeData['payed_fee']*1000) == ($params['total_price']*1000) )
            {
                $cancelParams['tid'] = $params['tid'];
                $cancelParams['shop_id'] = $params['shop_id'];
                $cancelParams['cancel_reason'] = $params['reason'] ? $params['reason'] : "取消订单退款";
                $cancelParams['refund_bn'] = $params['refund_bn'];
                app::get('sysaftersales')->rpcCall('trade.cancel.create',$cancelParams);
            }
            else
            {
                throw new \Exception("创建退款单失败，未发货订单，必须全额退款");
            }
        }

        return true;
    }
}



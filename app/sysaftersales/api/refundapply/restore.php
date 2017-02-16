<?php

class sysaftersales_api_refundapply_restore {

    /**
     * 接口作用说明
     */
    public $apiDescription = '平台对退款申请进行退款处理';

    public function getParams()
    {
        $return['params'] = array(
            'refunds_id' => ['type'=>'string','valid'=>'required', 'description'=>'退款申请单编号'],
            'return_fee' => ['type'=>'string','valid'=>'required', 'description'=>'退款金额'],
        );
        return $return;
    }

    public function update($params)
    {
        $filter['refunds_id'] = $params['refunds_id'];
        $objMdlRefunds = app::get('sysaftersales')->model('refunds');
        $refunds = $objMdlRefunds->getRow('refunds_id,refund_bn,status,aftersales_bn,hongbao_fee,user_hongbao_id,refund_fee,total_price,refunds_type,user_id,shop_id,tid,oid,return_freight',$filter);

        $db = app::get('sysaftersales')->database();
        $db->beginTransaction();

        try
        {
            //更新退款申请单
            $params['status'] ="1";
            $result = $objMdlRefunds->update($params,$filter);
            if(!$result)
            {
                throw new \LogicException(app::get('sysaftersales')->_('退款申请单更新失败'));
            }

            //如果为售后，则更新售后单状态
            if( $refunds['refunds_type'] == '0' )//退款类型，售后退款
            {
                $refundFee = $params['return_fee'];
                $this->__afsRefundSucc($refunds, $refundFee);
            }
            else//取消订单退款
            {
                //取消退款成功后，更新取消成功后操作
                app::get('sysaftersales')->rpcCall('trade.cancel.succ',['tid'=>$refunds['tid'],'shop_id'=>$refunds['shop_id'],'hongbao_fee'=>$refunds['hongbao_fee']]);

                // 如果是拒收订单，需要生成结算明细
                if($refunds['refunds_type'] == '2')
                {
                    $paramsClearing['tid'] = $refunds['tid'];
                    $paramsClearing['settlement_type'] = 4;
                    $result = app::get('sysaftersales')->rpcCall('clearing.detail.add',$paramsClearing);
                    if(!$result)
                    {
                        throw new \LogicException("拒收订单结算明细生成失败");
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            $db->rollback();
            throw new Exception($e->getMessage());
        }
        $db->commit();

        event::fire('refund.modified', [$refunds]);

        return true;
    }

    private function __afsRefundSucc($refunds, $refundFee)
    {
        $objMdlAftersales = app::get('sysaftersales')->model('aftersales');
        $aftersales = $objMdlAftersales->getRow('progress,status,tid,oid,user_id,shop_id',array('aftersales_bn'=>$refunds['aftersales_bn']));
        if($aftersales['tid'] != $refunds['tid'] || $aftersales['oid'] != $refunds['oid'] || $aftersales['user_id'] != $refunds['user_id'] || $aftersales['shop_id'] != $refunds['shop_id'])
        {
            throw new \LogicException(app::get('sysaftersales')->_('数据有误，请重新处理'));
        }

        if(in_array($aftersales['progress'],['3','4','6','7']) || in_array($aftersales['status'],['2','3']))
        {
            throw new \LogicException(app::get('sysaftersales')->_('当前处理异常，无法处理'));
        }

        $afterparams['progress'] = '7';
        $afterparams['status'] = '2';
        $afterFilter['aftersales_bn'] = $refunds['aftersales_bn'];
        $result = $objMdlAftersales->update($afterparams,$afterFilter);
        if(!$result)
        {
            throw new \LogicException(app::get('sysaftersales')->_('售后单状态更新失败'));
        }

        try
        {
            //更新字订单售后状态
            $orderparams['oid'] = $refunds['oid'];
            $orderparams['tid'] = $refunds['tid'];
            $orderparams['user_id'] = $refunds['user_id'];
            $orderparams['aftersales_status'] = 'SUCCESS';
            $orderparams['refund_fee'] = ecmath::number_minus(array($refundFee, $refunds['hongbao_fee']));
            $orderparams['total_fee'] = $refunds['total_price'];
            app::get('sysaftersales')->rpcCall('order.aftersales.status.update', $orderparams);

            $this->__rollbackHongbao($refunds);
        }
        catch( Exception $e)
        {
            throw new \LogicException($e->getMessage());
        }

        return true;
    }

    public function __rollbackHongbao($refunds)
    {
        if( $refunds['hongbao_fee'] <= 0 )
        {
            return true;
        }

        $params['user_id'] = $refunds['user_id'];
        $params['money'] = $refunds['hongbao_fee'];
        $params['hongbao_obtain_type'] = 'aftersales';
        $params['user_hongbao_id'] = $refunds['user_hongbao_id'];
        $params['tid'] = $refunds['tid'];

        return app::get('systrade')->rpcCall('user.hongbao.refund',$params);
    }
}

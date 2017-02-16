<?php
/**
 * ShopEx licence
 * - aftersales.refundapply.create
 * - 创建退款申请单
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class sysaftersales_api_refundapply_create {

    /**
     * 接口作用说明
     */
    public $apiDescription = '创建退款申请单';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'       => ['type'=>'string',   'valid'=>'required',  'title'=>'店铺ID',          'desc'=>'店铺ID'],
            'aftersales_bn' => ['type'=>'string',   'valid'=>'',          'title'=>'店铺ID',          'desc'=>'售后编号'],
            'tid'           => ['type'=>'string',   'valid'=>'required',  'title'=>'订单号',          'desc'=>'订单号'],
            'oid'           => ['type'=>'string',   'valid'=>'',          'title'=>'子订单号',        'desc'=>'子订单号，如果是取消订单则不需要输入'],
            'refunds_type'  => ['type'=>'string',   'valid'=>'required',  'title'=>'退款申请的类型',  'desc'=>'退款申请的类型,aftersalse 售后申请退款, cancel 取消订单退款'],
            'reason'        => ['type'=>'json',     'valid'=>'required',  'title'=>'申请退款理由',    'desc'=>'申请退款理由'],
            'total_price'   => ['type'=>'string',   'valid'=>'',          'title'=>'申请退款的金额',  'desc'=>'申请退款的金额，取消订单不需要填写退款金额'],
            'status'        => ['type'=>'string',   'valid'=>'required',  'title'=>'状态',            'desc'=>'商家创建退款则为商家强制关单，平台创建则回平台强制。0 未审核, 1 已完成退款,2 已驳回,3 商家审核通过, 4 商家审核不通过, 5 商家强制关单, 6 平台强制关单'],
            'refund_bn'     => ['type'=>'string',   'valid'=>'',          'title'=>'退款申请单编号',  'desc'=>'退款申请单编号，如果未填写则自动生成'],
            'return_freight'=> ['type'=>'bool',     'valid'=>'string',    'title'=>'是否返还运费',    'desc'=>'是否返还运费("true":退运费,"false":不退运费)'],
        );
        return $return;
    }

    /**
     * 判断是否可以进行创建退款申请单
     */
    private function __check($params)
    {
        $objMdlRefunds = app::get('sysaftersales')->model('refunds');
        if( $params['refunds_type'] == 'cancel' )
        {
            $data = $objMdlRefunds->getRow('refunds_id,status',['tid'=>$params['tid']]);
            if( $data && $data['status'] != '4' )
            {
                throw new \Exception("不能重复申请取消");
            }
        }
        else
        {
            if( empty($params['aftersales_bn']) )
            {
                throw new \Exception("该订单已申请过退款");
            }

            $data = $objMdlRefunds->getRow('refunds_id',['aftersales_bn'=>$params['aftersales_bn']]);
            if( $data )
            {
                throw new \Exception("该售后单已申请过退款");
            }
        }

        return true;
    }

    /**
     * 创建退款申请单
     * @desc 创建退款申请单
     * @return bool true 返回执行成功状态
     */
    public function create($params)
    {
        $this->__check($params);
        $objLibRefund = kernel::single('sysaftersales_refunds');

        if( $params['refunds_type'] == 'cancel' )
        {
            $data = $objLibRefund->cancelRefundApply($params['tid'], $params['status'], $params['reason'], $params['shop_id'], $params['refund_bn'],$params['return_freight']);
        }
        else
        {
            $db = app::get('sysuser')->database();
            $db->beginTransaction();
            try
            {
                //售后退款，如果是商家同意则表示提交到平台进行退款
                if( $params['status'] == '3' )
                {
                    $updateStatusParams['aftersales_bn'] = $params['aftersales_bn'];
                    $updateStatusParams['shop_id'] = $params['shop_id'];
                    $updateStatusParams['status'] = '8';
                    app::get('sysaftersales')->rpcCall('aftersales.status.update', $updateStatusParams);
                }
                $data = $objLibRefund->afsRefundApply($params, $params['tid'], $params['oid'], $params['refund_bn']);
            }
            catch( Exception $e )
            {
                $db->rollback();
                throw $e;
            }

            $db->commit();
        }

        event::fire('refund.created', [$params['tid'], $data]);

        return $data;
    }
}



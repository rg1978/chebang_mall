<?php

class systrade_api_trade_cancel_shopreply {

    /**
     * 接口作用说明
     */
    public $apiDescription = '商家审核取消订单';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'string','valid'=>'required', 'description'=>'店铺ID'],
            'cancel_id' => ['type'=>'string','valid'=>'required', 'description'=>'取消订单ID'],
            'status' => ['type'=>'string','valid'=>'required', 'description'=>'审核状态 agree 通过，reject 拒绝'],
            'reason' => ['type'=>'json','valid'=>'', 'description'=>'仅在审核不通过时填写该值,审核不通过原因'],
        );
        return $return;
    }

    public function reply($params)
    {
        if( $params['status'] == 'agree' )
        {
            //商家审核同意取消订单
            kernel::single('systrade_data_trade_cancel')->cancelShopAgree($params['cancel_id'], $params['shop_id']);
        }
        else
        {
            if( empty($params['reason']) )
            {
                throw new \LogicException('审核拒绝理由必填');
            }
            //商家审核拒绝取消订单
            kernel::single('systrade_data_trade_cancel')->cancelShopReject($params['cancel_id'], $params['shop_id'], $params['reason']);
        }

        return true;
    }
}


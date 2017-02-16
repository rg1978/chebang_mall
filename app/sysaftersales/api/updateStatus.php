<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 更新售后状态外部联通使用
 */
class sysaftersales_api_updateStatus {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新售后状态';

    /**
     * 消费者提交退货物流信息参数
     */
    public function getParams()
    {
        /*
         * 参数说明：corp_code 必填，但是如果值为other 则会判断是否有logi_name
         */
        $return['params'] = array(
            'aftersales_bn' => ['type'=>'string','valid'=>'required', 'description'=>'申请售后的订单编号'],
            'shop_id' => ['type'=>'string','valid'=>'required', 'description'=>'售后单所属店铺ID'],
            'status' => ['type'=>'string', 'valid'=>'required', 'description'=>'售后状态'],
            'memo' => ['type'=>'string', 'valid'=>'', 'description'=>'备注，如果售后为换货类型卖家重新发货信息必填在备注中'],
        );

        return $return;
    }

    /**
     * 消费者提交退货物流信息
     */
    public function update($params)
    {
        $filter['aftersales_bn'] = $params['aftersales_bn'];
        $filter['shop_id'] = $params['shop_id'];

        $objMdlAftersales = app::get('sysaftersales')->model('aftersales');
        $info = $objMdlAftersales->getRow('aftersales_bn,aftersales_type,progress,tid,oid,status,shop_id,user_id,reason',$filter);
        if( empty($info) )
        {
            throw new \LogicException(app::get('sysaftersales')->_('售后单号不存在'));
        }

        //平台退款不能直接接口进行更新
        if( in_array($params['status'],['6','7','0']) || ($params['status'] == '1' && $info['progress'] != '0') )
        {
            return true;
            //throw new \LogicException(app::get('sysaftersales')->_('更新的状态不存在'));
        }

        $progress = $params['status'];

        //如果是换货售后 OMS完成 那么在多用户商城上则为等待平台处理退款状态
        if( $progress == '4' && $info['aftersales_type'] != 'EXCHANGING_GOODS' )
        {
            $progress = '5';
        }

        if(in_array($progress, ['1','2','5','8']) )
        {
            $status = '1';
        }
        elseif( $progress == '4' )
        {
            $status = '2';
            if( empty($params['memo']) )
            {
                throw new \LogicException(app::get('sysaftersales')->_('卖家重新发货信息必填'));
            }

            $updateData['sendconfirm_data'] = serialize(['return_trade_info'=>$params['memo']]);
        }
        else
        {
            $status = '3';
        }

        try
        {
            $updateData['status'] = $status;
            $updateData['progress'] = $progress;
            $result = $objMdlAftersales->update($updateData, $filter);
        }
        catch( Exception $e )
        {
            throw new \LogicException(app::get('sysaftersales')->_('更新的状态失败'));
        }

        switch( $progress )
        {
        case '1':
            $params['aftersales_status'] =  "WAIT_BUYER_RETURN_GOODS";
            break;
        case '2':
            $params['aftersales_status'] =  "WAIT_SELLER_CONFIRM_GOODS";
            break;
        case '3':
            $params['aftersales_status'] =  "SELLER_REFUSE_BUYER";
            break;
        case '4':
            $params['aftersales_status'] =  "SELLER_SEND_GOODS";
            break;
        case '5':
            $params['aftersales_status'] =  "REFUNDING";
            break;
        }

        if( $params['aftersales_status'] )
        {
            $params['oid']=$info['oid'];
            $params['tid']=$info['tid'];
            $params['user_id']=$info['user_id'];
            app::get('sysaftersales')->rpcCall('order.aftersales.status.update', $params);
        }


        $this->__event($info, $params['shop_id']);

        return true;
    }

    private function __event($data, $shopId)
    {
        $eventData['aftersales_bn'] = $data['aftersales_bn'];
        $eventData['tid'] = $data['tid'];
        $eventData['oid'] = $data['oid'];
        $eventData['user_id'] = $data['user_id'];

        event::fire('aftersales.updateStatus', [$eventData, $shopId]);

        return true;
    }
}

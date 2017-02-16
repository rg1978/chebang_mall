<?php

class systrade_api_trade_cancel_get {

    public $apiDescription = "获取取消订单详情";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'', 'description'=>'会员ID'],
            'shop_id' => ['type'=>'int','valid'=>'', 'description'=>'店铺ID'],
            'cancel_id' => ['type'=>'string','valid'=>'required', 'description'=>'取消订单记录ID'],
        );
        return $return;
    }

    public function get($params)
    {
        $tradecancelRow = app::get('systrade')->model('trade_cancel')->getRow('*', ['cancel_id'=>$params['cancel_id']]);
        if( empty($tradecancelRow) ) return array();
        if( $params['user_id'] && $tradecancelRow['user_id'] !=  $params['user_id'] )
        {
            throw new \Exception("参数错误");//当前用户和取消记录中存储的用户ID不一致
        }

        if( $params['shop_id'] && $tradecancelRow['shop_id'] !=  $params['shop_id'] )
        {
            throw new \Exception("参数错误");//当前用户和取消记录中存储的用户ID不一致
        }

        $cancelLog = app::get('systrade')->model('log')->getList('*',['rel_id'=>$params['cancel_id'],'behavior'=>'cancel'], 0, -1, 'log_time asc');
        $tradecancelRow['log'] = $cancelLog;

        return $tradecancelRow;
    }
}


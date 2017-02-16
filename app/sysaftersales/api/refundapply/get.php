<?php

class sysaftersales_api_refundapply_get {

    /**
     * 接口作用说明
     */
    public $apiDescription = '根据退款申请单refunds_id，获取单个退款申请单详情';

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'string','valid'=>'required', 'description'=>'店铺ID'],
            'refunds_id' => ['type'=>'string','valid'=>'required', 'description'=>'退款申请单ID'],
        );
        return $return;
    }

    public function get($params)
    {
        $objMdlRefunds = app::get('sysaftersales')->model('refunds');
        $refundData = $objMdlRefunds->getRow('*', ['refunds_id'=>$params['refunds_id']]);
        return $refundData;
    }
}


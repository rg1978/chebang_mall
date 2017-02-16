<?php

class systrade_api_trade_cancel_succdo {

    public $apiDescription = "取消订单,退款成功后的操作";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'example'=>'','description'=>'订单id'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'example'=>'','description'=>'订单所属店铺id'],
            'hongbao_fee' => ['type'=>'int', 'valid'=>'', 'example'=>'','description'=>'退还红包的金额'],
        );

        return $return;
    }

    public function succdo($params)
    {
        //取消订单
        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try
        {
            kernel::single('systrade_data_trade_cancel')->cancelSuccDo($params['tid'], $params['shop_id'], $params['hongbao_fee']);

            $db->commit();
        }
        catch( LogicException $e)
        {
            $db->rollback();

            throw new \logicexception($e->getMessage());
        }

        return true;
    }
}


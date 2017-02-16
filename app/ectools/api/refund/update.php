<?php
class ectools_api_refund_update{

    public $apiDescription = '退款单更新';
    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'string','valid'=>'required', 'description'=>'申请售后的主订单编号', 'default'=>'', 'example'=>''],
            'refund_id' => ['type'=>'string','valid'=>'required', 'description'=>'退款单编号', 'default'=>'', 'example'=>''],
            'status' => ['type'=>'string','valid'=>'required', 'description'=>'退款状态', 'default'=>'', 'example'=>''],
        );
        return $return;
    }

    public function update($params)
    {
        $db = app::get('ectools')->database();
        $db->beginTransaction();
        try
        {
            $objRefund = kernel::single('ectools_data_refunds');
            $result = $objRefund->update(['status'=>$params['status']],['tid'=>$params['tid'],'refund_id'=>$params['refund_id']]);
            if(!$result)
            {
                throw new \LogicException(app::get('ectools')->_('退款单更新失败'));
            }
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw new \LogicException(app::get('ectools')->_($e->getMessage()));
            return false;
        }
        $db->commit();
        return true;
    }

}

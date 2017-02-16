<?php
/**
 * 商品上下架修改
 * item.sale.status
 */
class sysitem_api_item_updateStatus{

    public $apiDescription = "商品上下架修改";

    public function getParams($params)
    {
        $return['params'] = array(
            'item_id' => ['type'=>'int','valid'=>'','description'=>'商品id，多个id用，隔开','example'=>'2,3,5,6','default'=>''],
            'shop_id' => ['type'=>'int','valid'=>'','description'=>'店铺id','example'=>'','default'=>''],
            'approve_status' => ['type'=>'string','valid'=>'required','description'=>'商品上架状态','example'=>'','default'=>''],
        );
        return $return;
    }

    public function updateStatus($params)
    {
        
        $result = kernel::single('sysitem_data_item')->setSaleStatus($params);
        if($result)
        {
            event::fire('update.item', array($params['item_id']));
        }
        return $result;
    }

}

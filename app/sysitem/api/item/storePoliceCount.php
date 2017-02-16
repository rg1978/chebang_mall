<?php
class sysitem_api_item_storePoliceCount{
    public $apiDescription = "库存报警总数";
    public function getParams()
    {
        $return['params'] = array(
            'store' => ['type'=>'int','valid'=>'required','description'=>'库存数','example'=>'2','default'=>''],
            'shop_id' => ['type'=>'string','valid'=>'','description'=>'店铺id','example'=>'18'],
        );
        return $return;
    }
    public function storePolice($params)
    {
        $filter['store'] = $params['store'];
        $filter['shop_id'] = $params['shop_id'];

        $itemCount = kernel::single('sysitem_item_store')->getItemCountByStore($filter);
        return $itemCount;
    }
}



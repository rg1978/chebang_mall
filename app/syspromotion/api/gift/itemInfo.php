<?php
class syspromotion_api_gift_itemInfo{
    public $apiDescription = "获取参与活动的商品详情";
    public function getParams()
    {
        $data['params'] = array(
            'gift_id' => ['type'=>'int', 'valid'=>'sometimes|required|integer', 'default'=>'', 'example'=>'', 'description'=>'活动id'],
            'item_id' => ['type'=>'int', 'valid'=>'required|integer', 'default'=>'', 'example'=>'', 'description'=>'参加活动的商品id'],
            'valid' => ['type'=>'bool', 'valid'=>'boolean', 'default'=>'', 'example'=>'', 'description'=>'活动状态'],
        );
        return $data;
    }

    public function getInfo($params)
    {
        $data = array();
        $objItemGift = kernel::single('syspromotion_gift');
        if($params['valid'])
        {
            $itemFilter['start_time|lthan'] = time();
            $itemFilter['end_time|than'] = time();
            $itemFilter['status'] = '1';
            $data = $objItemGift->getGiftItemByItemId($params['item_id'],$itemFilter);
        }
        else
        {
            $itemFilter['gift_id'] = $params['gift_id'];
            if($itemFilter['gift_id'])
            {
                $data = $objItemGift->getGiftItemByItemId($params['item_id'],$itemFilter);
            }
        }
        $data = $data[0];

        if($data['gift_id'])
        {
            $gift_info = $objItemGift->getGiftInfo($data['gift_id'],'*');
            $data = array_merge($data,$gift_info);
        }

        if($data['gift_status'] == 'cancel')
        {
            return array();
        }
        return $data;
    }
}

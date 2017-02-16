<?php
class syspromotion_api_gift_giftItemGet{
	public $apiDescription = "获取参与活动有效的商品列表";
    public function getParams()
    {
        $data['params'] = array(
            'item_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'参加活动的商品id'],
            'valid' => ['type'=>'bool', 'valid'=>'boolean', 'default'=>'', 'example'=>'', 'description'=>'活动状态'],
            'start_time' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'sthan', 'description'=>'与开始时间相比，大于或小于指定时间,值为(sthan、bthan)'],
            'end_time' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'bthan', 'description'=>'与开结束相比，大于或小于指定时间,值为(sthan、bthan)'],
            'time' => ['type'=>'string', 'valid'=>'date', 'default'=>'', 'example'=>'2015-14-04 20:30', 'description'=>'指定时间(2015-14-04)'],

        );
        return $data;
    }

    public function getInfo($params)
    {
        $objItemGift = kernel::single('syspromotion_gift');
        if($params['start_time'])
        {
            $itemFilter['start_time|'.$params['start_time']] = $params['time'] ? strtotime($params['time']) : time();
            unset($params['start_time'],$params['time']);
        }

        if($params['end_time'])
        {
            $itemFilter['end_time|'.$params['end_time']] = $params['time'] ? strtotime($params['time']) : time();
            unset($params['end_time'],$params['time']);
        }

        if($params['valid'])
        {
            $itemFilter['status'] = '1';
        }

        $giftItem = $objItemGift->getGiftItemByItemId($params['item_id'],$itemFilter);
        return $giftItem;
    }
}

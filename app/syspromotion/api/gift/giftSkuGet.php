<?php

class syspromotion_api_gift_giftSkuGet {

	public $apiDescription = "获取作为赠品的商品";

    public function getParams()
    {
        $data['params'] = array(
            'item_id' => ['type'=>'string', 'valid'=>'', 'title'=>'参加活动的商品id', 'example'=>'', 'desc'=>'参加活动的商品id'],
            'sku_id' => ['type'=>'string', 'valid'=>'', 'title'=>'参加活动的货品id', 'example'=>'', 'desc'=>'参加活动的货品id'],
            'shop_id' => ['type'=>'string', 'valid'=>'sometimes|required|int', 'title'=>'店铺id', 'example'=>'', 'desc'=>'参加活动的货品id'],
            'valid' => ['type'=>'bool', 'valid'=>'sometimes|required|boolean', 'title'=>'活动状态', 'example'=>'', 'desc'=>'活动状态'],
            'start_time' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'sthan', 'description'=>'与开始时间相比，大于或小于指定时间,值为(sthan、bthan)'],
            'end_time' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'bthan', 'description'=>'与开结束相比，大于或小于指定时间,值为(sthan、bthan)'],
            'time' => ['type'=>'string', 'valid'=>'date', 'default'=>'', 'example'=>'2015-14-04 20:30', 'description'=>'指定时间(2015-14-04)'],

        );
        return $data;
    }

    public function getInfo($params)
    {
        unset($params['oauth']);
        if(!$params['item_id'] && !$params['sku_id'])
        {
            return array();
        }

        if($params['sku_id'])
        {
            $params['sku_id'] = explode(',',$params['sku_id']);
        }
        if($params['item_id'])
        {
            $params['item_id'] = explode(',',$params['item_id']);
        }

        $objMdlGiftSku = app::get('syspromotion')->model('gift_sku');

        if($params['start_time'])
        {
            $params['start_time|'.$params['start_time']] = $params['time'] ? strtotime($params['time']) : time();
            unset($params['start_time'],$params['time']);
        }

        if($params['end_time'])
        {
            $params['end_time|'.$params['end_time']] = $params['time'] ? strtotime($params['time']) : time();
            unset($params['end_time'],$params['time']);
        }

        if($params['valid'])
        {
            $params['status'] = '1';
            unset($params['valid']);
        }

        if($params['shop_id'])
        {
            $params['shop_id'] = $params['shop_id'];
        }

        $skuData = $objMdlGiftSku->getList('gift_id,sku_id,item_id',$params);
        return $skuData;
    }
}

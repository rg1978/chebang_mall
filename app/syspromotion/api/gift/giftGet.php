<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取赠品促销详情
 * promotion.gift.list
 */

final class syspromotion_api_gift_giftGet{

	public $apiDescription = '获取指定店铺的赠品促销详情';

    public function getParams()
    {
        $return['params'] = array(
            'gift_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'赠品促销id'],
            'gift_itemList' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'赠品促销的商品'],
        );

        return $return;
    }


	public function getGift($params)
	{
        $giftInfo = kernel::single('syspromotion_gift')->getGiftInfo($params['gift_id']);
        $giftInfo['valid'] = $this->__checkValid($giftInfo);
        if($params['gift_itemList'])
        {
            $giftItems = kernel::single('syspromotion_gift')->getgiftItems($params['gift_id']);
            $itemId = array_column($giftItems,'item_id');
            $searchParams = array(
                'item_id' => implode(',',$itemId),
                'fields' => 'item_id,title,shop_id,image_default_id,price',
            );
            $itemList = app::get('syspromotion')->rpcCall('item.search',$searchParams);
            $itemList = array_bind_key($itemList['list'],'item_id');
            foreach($giftItems as &$value)
            {
                if($itemList[$value['item_id']])
                {
                    $value['title'] = $itemList[$value['item_id']]['title'];
                    $value['price'] = $itemList[$value['item_id']]['price'];
                    $value['image_default_id'] = $itemList[$value['item_id']]['image_default_id'];
                }
            }
            $giftInfo['itemsList'] = $giftItems;

            $giftSkus = kernel::single('syspromotion_gift')->getgiftSku($params['gift_id']);
            $giftSkus = array_bind_key($giftSkus,'sku_id');
            $skuId = array_column($giftSkus,'sku_id');
            $searchParams = array(
                'sku_id' => implode(',',$skuId),
                'fields' => 'item_id,sku_id,title,spec_info,shop_id,image_default_id,store.*,item.shop_id,item.sub_stock,item.item_id,bn,status.item_id,status.approve_status',
            );
            $skuList = app::get('syspromotion')->rpcCall('sku.search',$searchParams);
            $skuList = $skuList['list'];
            foreach($giftSkus as &$value)
            {
                if($skuList[$value['sku_id']])
                {
                    $value = array_merge($value,$skuList[$value['sku_id']]);
                }
            }
            $giftInfo['gift_item'] = $giftSkus;
        }
        return $giftInfo;
	}

    // 检查组合促销是否可用
    private function __checkValid(&$giftInfo)
    {
        $now = time();
        if( ($giftInfo['gift_status']=='agree') && ($giftInfo['start_time']<$now) && ($giftInfo['end_time']>$now) )
        {
            return true;
        }
        return false;
    }
}

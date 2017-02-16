<?php
class syspromotion_gift extends syspromotion_abstract_promotions{
    public $promotionType = 'gift';
    public $promotionTag = '赠品';

    public function getGiftInfo($giftId,$row="*")
    {
        return app::get('syspromotion')->model('gift')->getRow($row, array('gift_id'=>$giftId));
    }

    public function getGiftItems($giftId)
    {
        return app::get('syspromotion')->model('gift_item')->getList('*', array('gift_id'=>$giftId));
    }

    public function getGiftSku($giftId)
    {
        return app::get('syspromotion')->model('gift_sku')->getList('*', array('gift_id'=>$giftId));
    }

    //获取赠品促销的商品
    public function getGiftItemByItemId($itemIds,$filter=array())
    {
	   	$giftItem = array();
        if($itemIds){
            if($filter)
            {
                $itemFilter = $filter;
            }
            $itemId = explode(',',$itemIds);
            $itemFilter['item_id'] = $itemId;
            $objMdlItemGift = app::get('syspromotion')->model('gift_item');
            $giftItem = $objMdlItemGift->getList('*', $itemFilter);
            if($giftItem)
            {
                $giftId = array_column($giftItem,'gift_id');
                $giftSkuList = $this->getGiftSku($giftId);

                //获取赠品商品数据
                $skuId = array_column($giftSkuList,'sku_id');
                $searchParams = array(
                    'sku_id' => implode(',',$skuId),
                    'fields' => 'item_id,sku_id,title,spec_info,shop_id,image_default_id,store.*,item.shop_id,item.sub_stock,item.item_id,bn,status.item_id,status.approve_status',
                );
                $skuList = app::get('syspromotion')->rpcCall('sku.search',$searchParams);
                $skuList = $skuList['list'];
                foreach($giftSkuList as $key=>&$value)
                {
                    if(isset($skuList[$value['sku_id']]['approve_status']) && $skuList[$value['sku_id']]['approve_status'] != "onsale" )
                    {
                        unset($giftSkuList[$key]);
                        continue;
                    }
                    if($skuList[$value['sku_id']])
                    {
                        $value = array_merge($value,$skuList[$value['sku_id']]);
                    }
                }

                if(!$giftSkuList)
                {
                    return array();
                }

                foreach($giftItem as $key=>&$value)
                {
                    foreach($giftSkuList as $k=>$val)
                    {
                        if($value['gift_id'] !=$val['gift_id'])
                        {
                            continue;
                        }
                        $value['gift_item'][] = $val;
                    }
                }
            }
        }
        return $giftItem;
    }


    public function saveGift($data)
    {
        $giftData = $this->__preareData($data);
        $objMdlgift = app::get('syspromotion')->model('gift');

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if($giftData['gift_item'])
            {
                $giftItem = $giftData['gift_item'];
                unset($giftData['gift_item']);
            }

            if( !$objMdlgift->save($giftData) )
            {
                throw \LogicException('赠品促销保存失败');
            }

            if(!$this->__saveGiftItem($giftData))
            {
                throw new \LogicException('赠品促销关联的商品信息保存失败!');
            }

            if(!$this->__saveGiftSku($giftData,$giftItem))
            {
                throw new \LogicException('赠品促销关联的商品赠品信息保存失败!');
            }
            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollback();
            throw $e;
        }
        return true;
    }

    private function __saveGiftItem($giftData)
    {
        $relItem = $giftData['rel_item_ids'];
        $searchParams = array(
            'item_id' => implode(',',$relItem),
            'fields' => 'item_id,title,image_default_id,cat_id,brand_id,price',
        );
        $itemsList = app::get('syspromotion')->rpcCall('item.search',$searchParams);
        if( empty($itemsList) ) return false;
        if($itemsList['total_found']>40)
        {
            $pages = ceil($itemsList['total_found']/40);
            for($i=2;$i<=$pages;$i++)
            {
                $searchParams = array(
                    'page_no' => $i,
                    'item_id' => implode(',',$relItem),
                    'fields' => 'item_id,title,image_default_id,cat_id,brand_id,price',
                );
                $itemsListData = app::get('syspromotion')->rpcCall('item.search',$searchParams);
                $itemsList['list'] = array_merge($itemsList['list'],$itemsListData['list']);
            }
        }

        $newItemsList = array();
        foreach($itemsList['list'] as $item)
        {
            $newItemsList[$item['item_id']] = $item;
        }

        $objMdlGiftItem = app::get('syspromotion')->model('gift_item');
        // 先删除赠品关联的商品
        $objMdlGiftItem->delete(array('gift_id'=>$giftData['gift_id']));
        foreach($giftData['rel_item_ids'] as $itemid)
        {
            if(!$newItemsList[$itemid])
            {
                continue;
            }
            $giftRelationItem = array(
                'gift_id' => $giftData['gift_id'],
                'item_id' => $itemid,
                'shop_id' => $giftData['shop_id'],
                'promotion_tag' => $this->promotionTag,
                'leaf_cat_id' => $newItemsList[$itemid]['cat_id'],
                'brand_id' => $newItemsList[$itemid]['brand_id'],
                'title' => $newItemsList[$itemid]['title'],
                'price' => $newItemsList[$itemid]['price'],
                'image_default_id' => $newItemsList[$itemid]['image_default_id'],
                'start_time' => $giftData['start_time'],
                'end_time' => $giftData['end_time'],
            );
            $objMdlGiftItem->save($giftRelationItem);
        }

        return true;
    }

    private function __saveGiftSku($giftData,$giftItem)
    {
        if(!$giftItem)
        {
            return false;
        }
        $giftId = $giftData['gift_id'];

        $objMdlGiftSku = app::get('syspromotion')->model('gift_sku');
        $objMdlGiftSku->delete(array('gift_id'=>$giftId));
        foreach($giftItem as $key=>$value)
        {
            $giftRelationSku = array(
                'gift_id' => $giftId,
                'sku_id' => $value['sku_id'],
                'item_id' => $value['item_id'],
                'shop_id' => $giftData['shop_id'],
                'gift_num' => $value['gift_num'],
                'start_time' => $giftData['start_time'],
                'end_time' => $giftData['end_time'],
            );
            $objMdlGiftSku->save($giftRelationSku);
        }
        return true;
    }

    private function __preareData($data)
    {
        $aResult = array();
        $aResult = $data;

        if($data['gift_id'])
        {
            $objMdlgift = app::get('syspromotion')->model('gift');
            $giftInfo = $objMdlgift->getRow('*', array('gift_id'=>$data['gift_id']));
            if(!app::get('sysconf')->getConf('shop.promotion.examine')){
                if( time() >= $giftInfo['start_time'] )
                {
                    throw new \LogicException('赠品促销生效时间内不可进行编辑!');
                }
            }else{
                if($giftInfo['gift_status'] =='pending'){
                    throw new \LogicException('赠品促销审核期间不可进行编辑！');
                }
                if($giftInfo['gift_status'] =='agree' ){
                    throw new \LogicException('已通过赠品促销审核不可进行编辑！');
                }
                if($giftInfo['gift_status'] =='cancel' ){
                    throw new \LogicException('已取消赠品促销不可进行编辑！');
                }
            }
        }
        else
        {
            $aResult['created_time'] = time();
        }
        if(!$data['gift_name'])
        {
            throw new \LogicException("赠品促销名称不能为空!");
        }
        if(!$data['gift_rel_itemids'])
        {
            throw new \LogicException("至少添加一个商品!");
        }

        $aResult['rel_item_ids'] = explode(',', $data['gift_rel_itemids']);
        $countAresult = count($aResult['rel_item_ids']);

        $objMdlgiftItem = app::get('syspromotion')->model('gift_item');
        $itemList = $objMdlgiftItem->getList('gift_id, title', array('item_id'=>$aResult['rel_item_ids'], 'end_time|than'=>$aResult['start_time'],'status'=>1));
        foreach($itemList as $v)
        {
            if($data['gift_id'] )
            {
                if($v['gift_id'] != $data['gift_id'])
                {
                    throw new \LogicException("商品 {$v['title']} 已经参加别的赠品促销，同一个商品只能应用于一个有效的赠品促销中！");
                }
            }
            else
            {
                throw new \LogicException("商品 {$v['title']} 已经参加别的赠品促销，同一个商品只能应用于一个有效的赠品促销中！");
            }
        }

        $data['gift_item'] = explode(',',$data['gift_item']);

        if(count($data['gift_item']) < 1 || count($data['gift_item']) > 4)
        {
            throw new \LogicException('赠品品类必须大于等于1小于等于4！');
        }
        $data['gift_item_info'] = json_decode($data['gift_item_info'],true);

        $aResult['gift_item'] = $this->__getGiftData($data['gift_item'],$data['gift_item_info'],$data['shop_id']);

        if( $data['start_time'] <= time() )
        {
            throw new \LogicException('赠品促销生效时间不能小于当前时间！');
        }
        if( $data['end_time'] <= $data['canuse_start_time'] )
        {
            throw new \LogicException('赠品促销结束时间不能小于开始时间！');
        }
        if( !$data['valid_grade'])
        {
            throw new \LogicException('至少选择一个会员等级');
        }

        $aResult['gift_name'] = strip_tags($data['gift_name']);
        $aResult['gift_desc'] = strip_tags($data['gift_desc']);
        $aResult['promotion_tag'] = $this->promotionTag;
        if(app::get('sysconf')->getConf('shop.promotion.examine')){
            $aResult['gift_status'] = 'non-reviewed';
        }else{
            $aResult['gift_status'] = 'agree';
        }

        return $aResult;
    }

    /*
     * 获取赠品相关数据
     */
    private function __getGiftData($skuData,$giftItemInfo,$shopId)
    {
        $searchParams = array(
            'sku_id' => implode(',',$skuData),
            'shop_id' => $shopId,
            'fields' => 'item_id,sku_id,title,spec_info,price,shop_id,image_default_id,store.*,item.shop_id,item.sub_stock,item.item_id,bn,status.item_id,status.approve_status',
        );
        $skuList = app::get('syspromotion')->rpcCall('sku.search',$searchParams);
        if(!$skuList)
        {
            throw new \LogicException('作为赠品的商品不存在!');
        }
        $giftItems = $skuList['list'];
        foreach($giftItemInfo as $key=>$value)
        {
            /*
                if($value['approve_status'] == "instock")
                {
                    throw new \LogicException('您有赠品处于下架状态!');
                }
             */
            $skuStore = $giftItems[$key]['realStore'];
            if($value > $skuStore)
            {
                throw new \LogicException('单个赠品的数量不能大于库存总数!');
            }
            unset($giftItems[$key]['store'],$giftItems[$key]['freez'],$giftItems[$key]['realStore']);
            $giftItems[$key]['gift_num'] = $value;
        }
        return $giftItems;
    }

    public function deleteGift($params)
    {
        $giftId = $params['gift_id'];
        if(!$giftId)
        {
            throw new \LogicException('赠品促销id不能为空！');
            return false;
        }

        $objMdlgift = app::get('syspromotion')->model('gift');
        $giftInfo = $objMdlgift->getRow('shop_id, start_time',array('gift_id'=>$giftId,'shop_id'=>$params['shop_id']));
        if( $giftInfo['shop_id'] != $params['shop_id'] )
        {
            throw new \LogicException('只能删除店铺所属的赠品促销！');
        }
        if(!app::get('sysconf')->getConf('shop.promotion.examine')){
            if( time() > $giftInfo['start_time'] )
            {
                throw new \LogicException('赠品促销生效后则不可删除！');
            }
        }
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();

        try
        {
            // 删除赠品主表数据
            if( !$objMdlgift->delete( array('gift_id'=>$giftId,'shop_id'=>$params['shop_id']) ) )
            {
                throw new \LogicException(app::get('syspromotion')->_('删除赠品促销信息失败'));
            }
            // 删除赠品关联的商品
            $objMdlgiftItem = app::get('syspromotion')->model('gift_item');
            if( !$objMdlgiftItem->delete( array('gift_id'=>$giftId,'shop_id'=>$params['shop_id']) ) )
            {
                throw new \LogicException(app::get('syspromotion')->_('删除赠品促销关联的商品失败'));
            }

            //删除赠品sku
            $objMdlgiftSku = app::get('syspromotion')->model('gift_sku');
            if( !$objMdlgiftSku->delete( array('gift_id'=>$giftId,'shop_id'=>$params['shop_id']) ) )
            {
                throw new \LogicException(app::get('syspromotion')->_('删除赠品促销包含的赠品失败'));
            }

            $db->commit();

        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }
        return true;
    }

}

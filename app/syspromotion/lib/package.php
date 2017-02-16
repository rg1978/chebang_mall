<?php

class syspromotion_package extends syspromotion_abstract_promotions {

    public $promotionType = 'package';
    public $promotionTag = '组合促销';

    public function getPackageList($filter)
    {
        $filter['shop_id'] = $filter['shop_id'];
        return app::get('syspromotion')->model('package')->getList('*', $filter, '0', '-1', 'package_id DESC');
    }

    public function getPackage($packageId)
    {
        return app::get('syspromotion')->model('package')->getRow('*', array('package_id'=>$packageId));
    }
    //根据组合促销id获取组合促销的所有商品
    public function getPackageItems($packageId)
    {
        return app::get('syspromotion')->model('package_item')->getList('*', array('package_id'=>$packageId));
    }

    /**
     * @brief 删除组合促销
     * @author lujy
     * @param $params array
     *
     * @return
     */
    public function deletePackage($params)
    {
        $packageId = $params['package_id'];
        if(!$packageId)
        {
            throw new \LogicException('组合促销促销id不能为空！');
            return false;
        }

        $objMdlPackage = app::get('syspromotion')->model('package');
        $packageInfo = $objMdlPackage->getRow('shop_id, start_time',array('package_id'=>$packageId,'shop_id'=>$params['shop_id']));
        if( $packageInfo['shop_id'] != $params['shop_id'] )
        {
            throw new \LogicException('只能删除店铺所属的组合促销促销！');
        }
        if(!app::get('sysconf')->getConf('shop.promotion.examine')){
            if( time() > $packageInfo['start_time'] )
            {
                throw new \LogicException('组合促销促销生效后则不可删除！');
            }
        }
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();

        try
        {
            // 删除组合促销主表数据
            if( !$objMdlPackage->delete( array('package_id'=>$packageId) ) )
            {
                throw new \LogicException(app::get('syspromotion')->_('删除组合促销失败'));
            }
            // 删除组合促销关联的商品
            $objMdlPackageItem = app::get('syspromotion')->model('package_item');
            if( !$objMdlPackageItem->delete( array('package_id'=>$packageId) ) )
            {
                throw new \LogicException(app::get('syspromotion')->_('删除组合促销失败'));
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

    /**
     * 保存组合促销促销
     * @param  array $data 组合促销促销传入数据
     * @return bool       是否保存成功
     */
    public function savePackage($data)
    {
        $packageData = $this->__preareData($data);
        $objMdlPackage = app::get('syspromotion')->model('package');

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if( !$objMdlPackage->save($packageData) )
            {
                throw \LogicException('组合促销保存失败');
            }
            if(!$this->__savePackageItem($packageData))
            {
                throw new \LogicException('组合促销促销关联商品保存失败!');
            }
            $db->commit();
        }
        catch(\LogicException $e)
        {
            $db->rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 保存组合促销促销关联的商品信息
     */
    private function __savePackageItem(&$packageData)
    {
        //ajx改为调用search接口
        $searchParams = array(
            'item_id' => implode(',',$packageData['rel_item_ids']),
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
                    'item_id' => implode(',',$packageData['rel_item_ids']),
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

        $packageItemsList = $packageData['package_item_list'];

        $objMdlPackageItem = app::get('syspromotion')->model('package_item');
        // 先删除组合促销关联的商品
        $objMdlPackageItem->delete(array('package_id'=>$packageData['package_id']));
        foreach($packageData['rel_item_ids'] as $itemid)
        {
            if(!$newItemsList[$itemid])
            {
                continue;
            }
            $packageRelationItem = array(
                'package_id' => $packageData['package_id'],
                'item_id' => $itemid,
                'shop_id' => $packageData['shop_id'],
                'promotion_tag' => $this->promotionTag,
                'leaf_cat_id' => $newItemsList[$itemid]['cat_id'],
                'brand_id' => $newItemsList[$itemid]['brand_id'],
                'package_price' => $packageItemsList[$itemid]['package_price'] ? $packageItemsList[$itemid]['package_price'] : $newItemsList[$itemid]['price'],
                'title' => $newItemsList[$itemid]['title'],
                'price' => $newItemsList[$itemid]['price'],
                'image_default_id' => $newItemsList[$itemid]['image_default_id'],
                'start_time' => $packageData['start_time'],
                'end_time' => $packageData['end_time'],
            );
            $objMdlPackageItem->save($packageRelationItem);
        }
        return true;
    }

    private function __preareData($data) {
        $aResult = array();
        $aResult = $data;

        if($data['package_id'])
        {
            $objMdlPackage = app::get('syspromotion')->model('package');
            $packageInfo = $objMdlPackage->getRow('*', array('package_id'=>$data['package_id']));
            if(!app::get('sysconf')->getConf('shop.promotion.examine')){
                if( time() >= $packageInfo['start_time'] )
                {
                    throw new \LogicException('组合促销生效时间内不可进行编辑!');
                }
            }else{
                if($packageInfo['package_status'] =='pending'){
                    throw new \LogicException('组合促销审核期间不可进行编辑！');
                }
                if($packageInfo['package_status'] =='agree' ){
                    throw new \LogicException('已通过组合促销审核不可进行编辑！');
                }
                if($packageInfo['package_status'] =='cancel' ){
                    throw new \LogicException('已取消组合促销不可进行编辑！');
                }
            }
        }
        else
        {
            $aResult['created_time'] = time();
        }
        if(!$data['package_name'])
        {
            throw new \LogicException("组合促销名称不能为空!");
        }
        if(!$data['package_rel_itemids'])
        {
            throw new \LogicException("至少添加一个商品!");
        }
        $aResult['rel_item_ids'] = explode(',', $data['package_rel_itemids']);
        $countAresult = count($aResult['rel_item_ids']);
        if($countAresult<2)
        {
            throw new \LogicException("最少添加2个商品!");
        }
        if($countAresult>10)
        {
            throw new \LogicException("最多添加10个商品!");
        }
        // $objMdlPackageItem = app::get('syspromotion')->model('package_item');
        // $itemList = $objMdlPackageItem->getList('package_id, title', array('item_id'=>$aResult['rel_item_ids'], 'end_time|than'=>time() ) );
        // foreach($itemList as $v)
        // {
        //     if($data['package_id'] )
        //     {
        //         if($v['package_id'] != $data['package_id'])
        //         {
        //             throw new \LogicException("商品 {$v['title']} 已经参加别的组合促销，同一个商品只能应用于一个有效的组合促销促销中！");
        //         }
        //     }
        //     else
        //     {
        //         throw new \LogicException("商品 {$v['title']} 已经参加别的组合促销，同一个商品只能应用于一个有效的组合促销促销中！");
        //     }
        // }

        if( $data['start_time'] <= time() )
        {
            throw new \LogicException('组合促销促销生效时间不能小于当前时间！');
        }
        if( $data['end_time'] <= $data['canuse_start_time'] )
        {
            throw new \LogicException('组合促销促销结束时间不能小于开始时间！');
        }
        if( !$data['valid_grade'])
        {
            throw new \LogicException('至少选择一个会员等级');
        }

        $aResult['package_name'] = strip_tags($data['package_name']);
        $aResult['package_desc'] = strip_tags($data['package_desc']);
        $aResult['package_total_price'] = strip_tags($data['package_total_price']);
        $forPlatform = intval($data['used_platform']);
        $aResult['used_platform'] = $forPlatform ? $forPlatform : '0';

        $aResult['promotion_tag'] = $this->promotionTag;
        if(app::get('sysconf')->getConf('shop.promotion.examine')){
            $aResult['package_status'] = 'non-reviewed';
        }else{
            $aResult['package_status'] = 'agree';
        }

        return $aResult;
    }

}

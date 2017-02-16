<?php

class systrade_cart_object_item implements systrade_interface_cart_object{

    //返回加入购物车的商品类型
    public function getObjType()
    {
        return 'item';//普通商品类型
    }

    //获取检查是否可以加入购物车排序，由小到大排序处理
    public function getCheckSort()
    {
        return 100;
    }

    public function basicFilter($params)
    {
        $obj_ident = $this->__genObjIdent($params);
        return array('obj_type'=>$params['obj_type'],'obj_ident'=>$obj_ident);
    }

    /**
     * @brief 检查购买的商品是否可以购买
     *
     * @param array $checkParams 加入购物车参数
     * @param array $itemData 加入购物车的基本商品数据
     * @param array $skuData 加入购物车的基本SKU数据
     *
     * @return bool
     */
    public function check($checkParams, $itemData, $skuData)
    {
        if( $checkParams['obj_type'] != 'item' ) return true;

        // 如果是更新购物车则获取sku_id
        if($checkParams['cart_id'] && !$checkParams['sku_id'])
        {
            $checkParams['sku_id'] = $basicCartData['0']['sku_id'];
        }
        //检查加入购物的商品是否有效
        if( empty($checkParams['sku_id']) )
        {
            throw new \LogicException(app::get('systrade')->_("缺少sku信息，无法加入购物车!"));
        }

        $this->objLibItemInfo = kernel::single('sysitem_item_info');
        $skuData = $this->objLibItemInfo->getSkuInfo($checkParams['sku_id']);

        if($checkParams['totalQuantity'] <=0)
        {
            throw new \LogicException(app::get('systrade')->_("库存不能为零，最小库存为1"));
        }
        //有效库存（可售库存）
        $validQuantity = $skuData['store'] - $skuData['freez'];
        if( $validQuantity < $checkParams['totalQuantity'] )
        {
            throw new \LogicException(app::get('systrade')->_("库存不足, 最大库存为".$validQuantity));
        }
        return true;
    }

    /**
     * 校验加入购物车数据是否符合要求-各种类型的数据的特殊性校验
     * @param array 加入购物车数据
     * @param string message 引用值
     * @return boolean true or false
     */
    public function checkObject($params, $basicCartData)
    {
        // 如果是更新购物车则获取sku_id
        if($params['cart_id'])
        {
            $params['sku_id'] = $basicCartData['0']['sku_id'];
            $params['quantity'] = $params['totalQuantity'];
        }
        else
        {
            $userId = $params['user_id'];
            $cartCount = kernel::single('systrade_data_cart', $userId)->countCart();
            if($cartCount['variety'] >= 50)
                throw new LogicException(app::get('systrade')->_('添加商品失败，已超出购物车最大容量！'));

        }

        //检查加入购物的商品是否有效
        if( empty($params['sku_id']) )
        {
            throw new \LogicException(app::get('systrade')->_("缺少sku信息，无法加入购物车!"));
        }

        if( !$params['quantity'] )
        {
            throw new \LogicException(app::get('systrade')->_("最少购买1件"));
        }
        $this->objLibItemInfo = kernel::single('sysitem_item_info');
        $skuData = $this->objLibItemInfo->getSkuInfo($params['sku_id']);

        $itemData = $this->objLibItemInfo->getItemInfo(array('item_id'=>$skuData['item_id']));
        //检测库存

        $validQuantity = $skuData['store'] - $skuData['freez'];
        if($params['totalQuantity'] > $validQuantity)
        {
            throw new \LogicException(app::get('systrade')->_("库存不足, 最大库存为".$validQuantity));
            return false;
        }

        //检查加入购物的商品是否有效
        if( !$this->__checkItemValid($itemData, $skuData) )
        {
            throw new \LogicException(app::get('systrade')->_("无效商品，加入购物车失败"));
        }

        return true;
    }

    /**
     * 检查加入购物车的商品是否有效
     *
     * @param array $itemsData 加入购物车的基本商品数据集合
     * @param array $skuData 加入购物车的基本SKU数据集合
     *
     * @return bool
     */
    private function __checkItemValid($itemsData, $skuData)
    {
        if( empty($itemsData) || empty($skuData) ) return false;

        //违规商品
        if( $itemsData['violation'] ) return false;

        //未启商品
        if( $itemsData['disabled'] ) return false;

        //未上架商品
        if($itemsData['approve_status'] != 'onsale' ) return false;

        //已删除SKU
        if( $skuData['status'] == 'delete' )
        {
            return false;
        }

        if( $skuData['store'] <= 0 || $skuData['realStore'] <=0 )
        {
            return false;
        }

        return true;
    }

    /**
     * @brief 加入购物车数据处理
     *
     * @param array $params 加入购物车基本（合并已有购物车）数据
     *
     * @return array
     */
    public function __preAddCartData($mergeParams, $userId, $basicCartData)
    {
        kernel::single('base_session')->start();
        $this->objMdlCart = app::get('systrade')->model('cart');
        $userIdent = $this->objMdlCart->getUserIdentMd5($userId);

        if( $mergeParams['cart_id'] )
        {
            $data['cart_id'] = $mergeParams['cart_id'];
            $mergeParams['sku_id'] = $basicCartData['0']['sku_id'];
        }
        else
        {
            $data['created_time'] = time();
        }

        $this->objLibItemInfo = kernel::single('sysitem_item_info');
        $skuData = $this->objLibItemInfo->getSkuInfo($mergeParams['sku_id']);
        $itemData = $this->objLibItemInfo->getItemInfo(array('item_id'=>$skuData['item_id']));

        // 是否购物车选中了
        $data['is_checked'] = $mergeParams['is_checked'];


        // 保存购物车选中的促销信息状态
        if(isset($mergeParams['selected_promotion']))
        {
            $data['selected_promotion'] = intval($mergeParams['selected_promotion']);
        }
        // else
        // {
        //     $data['selected_promotion'] = '0';
        // }
        $data['user_id'] = $mergeParams['user_id'];
        $data['user_id'] = $data['user_id'] ? $data['user_id'] : '-1';
        $data['user_ident'] = $userIdent;
        $data['shop_id'] = $itemData['shop_id'];
        $data['obj_type'] = $mergeParams['obj_type'] ? $mergeParams['obj_type'] : 'item';
        $data['obj_ident'] = $this->__genObjIdent($mergeParams);
        $data['item_id'] = $itemData['item_id'];
        $data['sku_id'] = $mergeParams['sku_id'];
        $data['title'] = $skuData['title'];
        $data['image_default_id'] = $itemData['image_default_id'];
        $data['quantity'] = $mergeParams['totalQuantity'];

        // 活动，剩余购买数量
        $activityBuyInfo = $this->activityBuyInfo($itemData['item_id'], $data['user_id']);
        if( $activityBuyInfo['ifactivity'] )
        {
            if($mergeParams['totalQuantity'] >= $activityBuyInfo['restActivityNum'])
            {
                $data['quantity'] = $activityBuyInfo['restActivityNum']>0 ? $activityBuyInfo['restActivityNum'] : 0;
            }
        }

        $data['modified_time'] = time();
        return $data;
    }

    // 活动剩余购买数量,购物车结构改造，临时存放这里，此方法目前和systrade_data_cart里面的方法一致
    public function activityBuyInfo($itemId, $userId)
    {
        // 活动，剩余购买数量
        $promotionDetail = app::get('systrade')->rpcCall('promotion.activity.item.info',array('item_id'=>$itemId, 'valid'=>1), 'buyer');
        if($promotionDetail['item_id'])
        {
            $objMdlPromDetail = app::get('systrade')->model('promotion_detail');
            $filter = array('promotion_id'=>$promotionDetail['activity_id'], 'promotion_type'=>'activity', 'user_id'=>$userId, 'item_id'=>$itemId);
            $oids = $objMdlPromDetail->getList('oid,item_id', $filter);
            $objMdlOrder = app::get('systrade')->model('order');
            $activityNum = 0;
            foreach($oids as $v)
            {
                $orderInfo = $objMdlOrder->getRow('status,num',array('oid'=>$v['oid']));
                if( !in_array( $orderInfo['status'], array('TRADE_CLOSED_BY_SYSTEM', 'TRADE_CLOSED') ) )
                {
                    $activityNum += $orderInfo['num'];
                }
            }
            $restActivityNum = $promotionDetail['activity_info']['buy_limit']-$activityNum;
            return array('ifactivity'=>$promotionDetail['item_id']?true:false,'restActivityNum'=>$restActivityNum, 'activityInfo'=>$promotionDetail);
        }
    }

    // 处理一条购物车object信息
    public function processCartObject($row,$itemsData,$skusData)
    {
        $itemId = $row['item_id'];
        $skuId = $row['sku_id'];
        $userId = $row['user_id'];
        $objectData['cart_id'] = $row['cart_id'];
        $objectData['obj_type'] = $row['obj_type'];
        $objectData['item_id'] = $itemId;
        $objectData['sku_id'] = $skuId;
        $objectData['user_id'] = $userId;
        $objectData['selected_promotion'] = $row['selected_promotion'];
        $objectData['cat_id'] = $itemsData[$itemId]['cat_id'];
        $objectData['sub_stock'] = $itemsData[$itemId]['sub_stock'];
        $objectData['spec_info'] = $skusData[$skuId]['spec_info'];
        $objectData['bn'] = $skusData[$skuId]['bn'];
        $objectData['dlytmpl_id'] = $itemsData[$itemId]['dlytmpl_id'];
        //可售库存
        $objectData['store'] = $skusData[$skuId]['realStore'];
        $objectData['status'] = $itemsData[$itemId]['approve_status'];
        // 初始状态下折扣金额从0开始
        $objectData['price']['discount_price'] = 0;
        // 活动，剩余购买数量,如果剩余
        $activityBuyInfo = $this->activityBuyInfo($itemId, $userId);
        if( $activityBuyInfo['ifactivity'] )
        {
            if($row['quantity'] >= $activityBuyInfo['restActivityNum'])
            {
                $row['quantity'] = $activityBuyInfo['restActivityNum']>0 ? $activityBuyInfo['restActivityNum'] : 0;
            }
        }
        $objectData['quantity'] = $row['quantity'];//购买数量
        $objectData['title'] = $itemsData[$itemId]['title'] ? $itemsData[$itemId]['title'] : $row['title'];
        $objectData['image_default_id'] = $itemsData[$itemId]['image_default_id'] ? $itemsData[$itemId]['image_default_id'] : $row['image_default_id'];
        $objectData['weight'] = ecmath::number_multiple(array($skusData[$skuId]['weight'],$row['quantity']));
        $activityDetail = $activityBuyInfo['activityInfo'];
        if($activityDetail['activity_price']>0)
        {
            $objectData['price']['price'] = $activityDetail['activity_price']; //购买促销后单价
            $objectData['price']['total_price'] = ecmath::number_multiple(array($activityDetail['activity_price'],$row['quantity'])); //购买此SKU总价格

            $oldTotalPrice = ecmath::number_multiple(array($skusData[$skuId]['price'],$row['quantity'])); //购买此SKU总价格
            // 平台活动不能在这里计算折扣金额，会导致计算子订单优惠分摊出错
            // $objectData['price']['discount_price'] = ecmath::number_minus(array($oldTotalPrice, $objectData['price']['total_price']));
            $objectData['activityDetail'] = $activityDetail;
            $objectData['promotion_type'] = 'activity'; //活动类型（针对单品），
        }
        else
        {
            $objectData['price']['price'] = $skusData[$skuId]['price']; //购买促销前单价
            $objectData['price']['total_price'] = ecmath::number_multiple(array($skusData[$skuId]['price'],$row['quantity'])); //购买此SKU总价格
        }

        if($row['obj_type']!='package')
        {
            $objectData['valid'] = $this->__checkItemValid($itemsData[$itemId], $skusData[$skuId] );//是否为有效数据
        }
        // 如果可购买数量小于等于0（一般是活动限购会导致此情况），则商品失效
        if($objectData['quantity']<=0)
        {
            $objectData['valid'] = false;
        }
        if($objectData['valid'])
        {
            $objectData['is_checked'] = $row['is_checked'];
        }
        else
        {
            $objectData['is_checked'] = 0;
        }
        return $objectData;
    }

    // 购物车的唯一信息进行判别是添加还是更新购物车，
    private function __genObjIdent(&$aData) {
        return $this->getObjType().'_'.$aData['sku_id'];
    }

    // 保存购物车主表cart_objects的时候，把对应的sku数量信息保存到cart_item表,方便库存判断
    public function __afterSaveCart($fullCartData)
    {
        $data['cart_id'] = $fullCartData['cart_id'];
        $data['sku_id'] = $fullCartData['sku_id'];
        $data['quantity'] = $fullCartData['quantity'];
        return app::get('systrade')->model('cart_item')->save($data);
    }

    // 返回运费计算需要的基本信息
    public function getInfoForPost(&$cartInfo)
    {
        $result[] = array(
            'dlytmpl_id' => $cartInfo['dlytmpl_id'],
            'total_quantity' => $cartInfo['quantity'],
            'total_weight' => $cartInfo['weight'],
            'total_price' => ecmath::number_minus( array($cartInfo['price']['total_price'], $cartInfo['price']['discount_price']) ),
        );
        return $result;
    }

}


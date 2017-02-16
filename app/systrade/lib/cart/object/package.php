<?php

class systrade_cart_object_package implements systrade_interface_cart_object{

    //返回加入购物车的商品类型
    public function getObjType()
    {
        return 'package';//组合促销商品类型
    }

    //获取检查是否可以加入购物车排序，由小到大排序处理
    public function getCheckSort()
    {
        return 200;
    }

    public function basicFilter($params)
    {
        $obj_ident = $this->__genObjIdent($params);
        return array('obj_type'=>$params['obj_type'],'obj_ident'=>$obj_ident);
    }

    /**
     * @brief 检查购买的商品是否可以购买
     *
     * @param array $params 加入购物车参数
     * @param array $itemData 加入购物车的基本商品数据
     * @param array $skuData 加入购物车的基本SKU数据
     *
     * @return bool
     */
    public function check($checkParams, $itemData, $skuData)
    {
        if( $checkParams['obj_type'] != 'package' ) return true;

        if($checkParams['totalQuantity'] <=0)
        {
            throw new \LogicException(app::get('systrade')->_("库存不能为零，最小库存为1"));
        }
        //有效库存（可售库存）
        // $validQuantity = $skuData['store'];
        // if( $validQuantity < $checkParams['totalQuantity'] )
        // {
        //     throw new \LogicException(app::get('systrade')->_("库存不足, 最大库存为".$validQuantity));
        // }
        return true;
    }

    /**
     * @brief 检查购买的组合商品是否可以购买
     *
     * @param array $params 加入购物车参数
     * @param array $itemData 加入购物车的基本商品数据
     * @param array $skuData 加入购物车的基本SKU数据
     *
     * @return bool
     */
    public function checkObject($params,$basicCartData)
    {
        if($params['cart_id'] && !$params['package_id'])
        {
            $params['obj_type'] = $basicCartData['0']['obj_type'];
            $params['package_id'] = $basicCartData['0']['package_id'];
            $params['package_sku_ids'] = implode(',',$basicCartData['0']['params']['sku_ids']);
            $params['quantity'] = $params['totalQuantity'];
        }
        $objLibItemInfo = kernel::single('sysitem_item_info');
        $filter = array('package_id'=>$params['package_id']);
        //组合促销信息
        $packageInfo = app::get('systrade')->rpcCall('promotion.package.get', $filter);
        //会员信息
        $userInfo = app::get('systrade')->rpcCall('user.get.info', array('user_id'=>$params['user_id']),'buyer');
        //组合促销商品信息
        $packageParams = array(
            'page_no' => 1,
            'page_size' => 10,
            'fields' =>'item_id',
            'package_id' => $params['package_id'],
        );
        $packageItemList = app::get('topc')->rpcCall('promotion.packageitem.list', $packageParams);
        foreach ($packageItemList['list'] as $key => $value)
        {
            $itemIds[] = $value['item_id'];
        }
        //组合促销货品信息
        $skuIds = explode(',',$params['package_sku_ids']);
        $skuRows = 'item_id';
        $skusData = $objLibItemInfo->getSkusList($skuIds,$skuRows);

        //判断加入的货品是否在组合促销里面
        foreach ($skusData as $key => $value)
        {
            if(!in_array($value['item_id'],$itemIds))
            {
                throw new \LogicException(app::get('systrade')->_("该货品不在组合促销范围内!"));
            }
        }
        $realStore = min(array_column($skusData,'realStore'));
        //检查加入购物的组合商品是否有效
        if( empty($params['package_id']) )
        {
            throw new \LogicException(app::get('systrade')->_("缺少组合促销信息，无法加入购物车!"));
        }
        if( !$params['totalQuantity'] )
        {
            throw new \LogicException(app::get('systrade')->_("最少购买1件"));
        }
        if($params['totalQuantity']>$realStore)
        {
            throw new \LogicException(app::get('systrade')->_("库存不足!最小库存为".$realStore));
        }
        if(!in_array($userInfo['grade_id'],explode(',',$packageInfo['valid_grade'])))
        {
            throw new \LogicException(app::get('systrade')->_("您的会员等级不够!"));
        }
        if($packageInfo['package_status']!='agree')
        {
            throw new \LogicException(app::get('systrade')->_("该促销不能购买!"));
        }
        if($packageInfo['end_time'] < time())
        {
            throw new \LogicException(app::get('systrade')->_("该促销已经结束!"));
        }
        if($packageInfo['start_time'] > time())
        {
            throw new \LogicException(app::get('systrade')->_("该促销还没有开始!"));
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
        if( $mergeParams['cart_id'] )
        {
            $data['cart_id'] = $mergeParams['cart_id'];
            $mergeParams['obj_type'] = $basicCartData['0']['obj_type'];
            $mergeParams['package_id'] = $basicCartData['0']['package_id'];
            if(!$mergeParams['package_sku_ids'])
            {
                $mergeParams['package_sku_ids'] = implode(',', $basicCartData['0']['params']['sku_ids']);
            }
        }
        else
        {
            $data['created_time'] = time();
        }
        kernel::single('base_session')->start();
        $this->objMdlCart = app::get('systrade')->model('cart');
        $userIdent = $this->objMdlCart->getUserIdentMd5($userId);
        $filter = array('package_id'=>$mergeParams['package_id']);
        $packageInfo = app::get('systrade')->rpcCall('promotion.package.get', $filter);

        // 是否购物车选中了
        $data['is_checked'] = $mergeParams['is_checked'];
        $data['package_id'] = $mergeParams['package_id'];
        // 保存购物车选中的促销信息状态
        $data['selected_promotion'] = 0;
        $data['user_id'] = $mergeParams['user_id'];
        $data['user_id'] = $data['user_id'] ? $data['user_id'] : '-1';
        $data['user_ident'] = $userIdent;
        $data['shop_id'] = $packageInfo['shop_id'];
        $data['obj_type'] = 'package';
        $data['obj_ident'] = $this->__genObjIdent($mergeParams);
        $data['title'] = $packageInfo['package_name'];
        $data['quantity'] = $mergeParams['totalQuantity'];
        $data['params'] = array('package_id'=>$mergeParams['package_id'], 'sku_ids'=>explode(',',$mergeParams['package_sku_ids']));
        $data['modified_time'] = time();
        return $data;
    }

    // 处理一条购物车object信息
    public function processCartObject($row,$itemsData,$skusData)
    {
        $filter = array('package_id'=>$row['package_id']);
        $packageInfo = app::get('systrade')->rpcCall('promotion.package.get', $filter);
        $params = array(
            'page_no' => 1,
            'page_size' => 10,
            'fields' =>'*',
            'package_id' => $row['package_id'],
        );
        $packageItemList = app::get('topc')->rpcCall('promotion.packageitem.list', $params);
        $packageItemList = array_bind_key($packageItemList['list'], 'item_id');
        $packageItemIds = array_column($packageItemList, 'item_id');

        $itemRows = 'item_id,cat_id,title,weight,image_default_id,sub_stock,violation,disabled,dlytmpl_id';
        $skuRows = 'sku_id,bn,item_id,spec_info,price,weight,status';
        $itemFields['status'] = 'approve_status';
        $this->objLibItemInfo = kernel::single('sysitem_item_info');
        $itemsData = $this->objLibItemInfo->getItemList($packageItemIds, $itemRows, $itemFields);
        $skusData = $this->objLibItemInfo->getSkusList($row['params']['sku_ids'], $skuRows);
        $skuCartInfo = [];
        // 初始状态下折扣金额从0开始
        $total_discount_price = $discount_price = $total_weight = $total_price = $inValidSkuNum = 0;

        //会员信息
        $userInfo = app::get('systrade')->rpcCall('user.get.info', array('user_id'=>$row['user_id']),'buyer');
        foreach($skusData as $skuId=>$v)
        {
            if( !in_array($userInfo['grade_id'],explode(',',$packageInfo['valid_grade'])) )
            {
                $skuCartInfo[$skuId]['valid'] = false;
            }
            else
            {
                $skuCartInfo[$skuId]['valid'] = $this->__checkItemValid( $itemsData[$v['item_id']], $skusData[$v['sku_id']] );//是否为有效数据
            }

            if($skuCartInfo[$skuId]['valid']) $inValidSkuNum++;
            $skuCartInfo[$skuId]['item_id'] = $v['item_id'];
            $skuCartInfo[$skuId]['sku_id'] = $v['sku_id'];
            $skuCartInfo[$skuId]['cat_id'] = $itemsData[$v['item_id']]['cat_id'];
            $skuCartInfo[$skuId]['sub_stock'] = $itemsData[$v['item_id']]['sub_stock'];
            $skuCartInfo[$skuId]['dlytmpl_id'] = $itemsData[$v['item_id']]['dlytmpl_id'];
            $skuCartInfo[$skuId]['status'] = $itemsData[$v['item_id']]['approve_status'];
            $skuCartInfo[$skuId]['spec_info'] = $v['spec_info'];
            $skuCartInfo[$skuId]['bn'] = $v['bn'];
            $skuCartInfo[$skuId]['title'] = $itemsData[$v['item_id']]['title'];
            $skuCartInfo[$skuId]['store'] = $v['realStore'];
            $skuCartInfo[$skuId]['weight'] = $v['weight'];
            $skuCartInfo[$skuId]['price']['old_price'] = $v['price'];
            $skuCartInfo[$skuId]['price']['price'] = $packageItemList[$v['item_id']]['package_price'];
            $skuCartInfo[$skuId]['image_default_id'] = $itemsData[$v['item_id']]['image_default_id'];
            $total_price += $packageItemList[$v['item_id']]['package_price'];
            $total_weight += $v['weight'];
            // 暂时不做优惠价记录，设为0
            $discount_price = 0;//ecmath::number_minus(array($v['price'],$packageItemList[$v['item_id']]['package_price']));
            $skuCartInfo[$skuId]['price']['discount_price'] = $discount_price;
            $total_discount_price += $discount_price;
        }

        $minCanBuyNum = min(array_column($skusData, 'realStore'));
        if($row['quantity'] > $minCanBuyNum)
        {
            $row['quantity'] = $minCanBuyNum; //如果购物车的商品数量大于组合促销其中最小的sku的可售库存则将购买数量改成最小的
        }

        $objectData['cart_id'] = $row['cart_id'];
        $objectData['obj_type'] = $row['obj_type'];
        $objectData['store'] = $minCanBuyNum;
        $objectData['valid'] = ($packageInfo['valid'] && $inValidSkuNum) ? true : false;
        $objectData['selected_promotion'] = 0;//组合促销商品不参加其他促销
        $objectData['quantity'] = $row['quantity'];//购买数量
        $objectData['title'] = $packageInfo['title'] ? $packageInfo['title'] : $row['title'];
        $objectData['image_default_id'] = '';
        $objectData['weight'] = ecmath::number_multiple(array($total_weight, $row['quantity']));
        $objectData['price']['price'] = $total_price; //一个组合促销的各商品的价格和
        // 暂时不做优惠价记录，设为0
        $objectData['price']['discount_price'] = 0;//ecmath::number_multiple(array($total_discount_price,$row['quantity']));
        $objectData['price']['total_price'] = ecmath::number_multiple(array($total_price, $row['quantity'])); //购买此组合促销的sku促销价格总和乘以数量
        $objectData['skuList'] = $skuCartInfo;
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

        if( $skuData['store'] <= 0 )
        {
            return false;
        }

        return true;
    }

    // 购物车的唯一信息进行判别是添加还是更新购物车，
    private function __genObjIdent(&$aData) {
        $skuidsArr = explode(',',$aData['package_sku_ids']);
        $ident = $this->getObjType().'_'.$aData['package_id'];
        sort($skuidsArr);
        return $ident.'_'.implode('-', $skuidsArr);
    }

    // 保存购物车主表cart_objects的时候，把对应的sku数量信息保存到cart_item表,方便库存判断
    public function __afterSaveCart($fullCartData)
    {
        foreach($fullCartData['params']['sku_ids'] as $skuid)
        {
            $data['cart_id'] = $fullCartData['cart_id'];
            $data['quantity'] = $fullCartData['quantity'];
            $data['sku_id'] = $skuid;
            if(!app::get('systrade')->model('cart_item')->save($data)) return false;
        }
        return true;
    }

    // 返回运费计算需要的基本信息
    public function getInfoForPost(&$cartInfo)
    {
        $result = [];
        foreach ($cartInfo['skuList'] as $v)
        {
            $result[] = array(
                'dlytmpl_id' => $v['dlytmpl_id'],
                'total_quantity' => $cartInfo['quantity'],
                'total_weight' => ecmath::number_multiple( array($v['weight'], $cartInfo['quantity']) ),
                'total_price' => ecmath::number_multiple( array($v['price']['price'], $cartInfo['quantity']) ),
            );
        }
        return $result;
    }

}


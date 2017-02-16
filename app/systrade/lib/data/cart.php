<?php

class systrade_data_cart {

    public $objects = array();

	/**
	 * user Id
	 *
	 * @var int
	 */
    protected $userId = null;

    public function __construct($userId)
    {
        if (!$userId) throw new \InvalidArgumentException('user id cannot null.');
        $this->userId = $userId;
        $this->objMdlCart = app::get('systrade')->model('cart');
        $this->objLibItemInfo = kernel::single('sysitem_item_info');
        $this->__instance();
    }

    //初始化加入购物车的处理类
    private function __instance()
    {
        if( !$this->objects )
        {
            $objs = kernel::servicelist('cart_object_apps');
            foreach( $objs as $obj )
            {
                //购物车类型
                $type = $obj->getObjType();
                //排序
                $index = $obj->getCheckSort();
                if( isset($tmp[$index]) ) $index++;

                $tmpIndex[$index] = $type;
                $tmpObjects[$type] = $obj;
            }
            ksort($tmpIndex);
            foreach( $tmpIndex as $type )
            {
                $this->objects[$type] = $tmpObjects[$type];
            }
        }
        return $this->objects;
    }

    /**
     * @brief 检查是否可以购买
     *
     * @param array $params 加入购物车参数
     * @param array $itemData 加入购物车的基本商品数据
     * @param array $skuData 加入购物车的基本SKU数据
     *
     * @return bool
     */
    private function __check($checkParams, $itemData, $skuData)
    {
        foreach( $this->objects as $obj )
        {
            $obj->check($checkParams, $itemData, $skuData);
        }
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

    /**
     * 如果加入购物车的商品在购物车中已存在，则进行合并
     *
     * @param $cartBasicData 根据加入购物车的的参数，获取到的基本的购物车数据
     * @param $params  加入购物车的的参数
     *
     * @return array
     */
    private function __mergeAddCartData($cartBasicData, $params)
    {
        //购买方式分为加入购物车模式，和立即购买模式
        //加入购物车模式需要判断购物车是否已经有该次购买的商品
        if( $cartBasicData && (empty($params['mode']) || $params['mode'] == 'cart') )
        {
            //总购买数量
            $params['totalQuantity'] = $params['quantity'];
            if( $params['obj_type'] && $cartBasicData['obj_type'] == $cartBasicData['obj_type'] )
            {
                $params['totalQuantity'] += intval($cartBasicData['quantity']);
                $params['cart_id'] = $cartBasicData['cart_id'];
            }
        }
        else
        {
            $params['totalQuantity'] = intval($params['quantity']);
        }

        return $params;
    }

    /**
     * @brief 加入购物车
     *
     * @param array $params 加入购物车参数
     *
     * @return bool
     */
    public function addCart($params)
    {
        if (!$this->objects[$params['obj_type']])
        {
            $msg = app::get('systrade')->_('商品类型错误！');
            throw new \LogicException($msg);
        }

        // 不同的cart  objects查询参数各自定义
        $filter = $this->objects[$params['obj_type']]->basicFilter($params);
        $cartBasicData = $this->getBasicCart($filter);
        // 合并购物车，再判断
        $mergeParams = $this->__mergeAddCartData($cartBasicData[0], $params);
        // 进行各自的特殊校验
        $this->objects[$params['obj_type']]->checkObject($mergeParams, $cartBasicData);
        // 判断合并后的购物车信息
        $this->__check($mergeParams);
        // 格式化加入购物车数据
        $data = $this->objects[$params['obj_type']]->__preAddCartData($mergeParams, $this->userId, $cartBasicData);

        if( $params['mode'] == 'fastbuy' )
        {
            return $this->fastBuyStore($data);
        }
        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try
        {
            $result = $this->objMdlCart->save($data);
            // $this->objects[$params['obj_type']]->__afterSaveCart($data);
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return $result ? $data : false;
    }

    public function countCart()
    {
        $filter['user_ident'] = $this->objMdlCart->getUserIdentMd5($this->userId);
        $number = 0;
        $data = $this->objMdlCart->getList('quantity',$filter);
        $cartNumber['variety'] = count($data);
        foreach($data as $val)
        {
            $number += $val['quantity'];
        }
        $cartNumber['number'] = $number;
        return $cartNumber;
    }

    public function setCartCookieNum($cartNumber)
    {
        $cookie_path = kernel::base_url().'/';
        $expire = time()+315360000;
        setcookie('CARTNUMBER',$cartNumber,$expire,$cookie_path);
        return true;
    }

    /**
     * @brief 立即购买流程存储购物数据
     *
     * @param array $params
     *
     * @return bool
     */
    public function fastBuyStore($params)
    {
        kernel::single('base_session')->start();
        $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
        $params['is_checked'] = '1';
        $_SESSION['cart_objects_fastbuy'][$userIdent] = $params;
        kernel::single('base_session')->close();
        return true;
    }

    public function fastBuyFetch()
    {
        $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
        return $_SESSION['cart_objects_fastbuy'][$userIdent];
    }

    /**
     * @brief 更新购物车信息
     *
     * @param array $params
     *
     * @return bool
     */
    public function updateCart($params)
    {
        if( $params['mode'] == 'fastbuy' ) return false;

        if( !$params['cart_id'] )
        {
            throw new \LogicException(app::get('systrade')->_("购物车发生变化，请刷新页面！"));
        }

        // 进行各自的特殊校验
        $filter['cart_id'] = $params['cart_id'];
        $filter['user_id'] = $params['user_id'];
        $basicCartData = $this->getBasicCart($filter);
        $params['obj_type'] = $basicCartData['0']['obj_type'];
        if( !$basicCartData )
        {
            throw new \LogicException(app::get('systrade')->_("购物车发生变化，请刷新页面！"));
        }
        //检查商品是否能加入购物车
        $this->objects[$params['obj_type']]->checkObject($params, $basicCartData);
        //这里是格式化购物车数据（__preAddCartData方法）
        $data = $this->objects[$params['obj_type']]->__preAddCartData($params, $this->userId, $basicCartData);

        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try
        {
            $result = $this->objMdlCart->save($data);
            // $this->objects[$params['obj_type']]->__afterSaveCart($data);
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return $result;
    }

    /**
     * @brief 根据条件删除购物车信息，如果条件为空，则清空购物车
     *
     * @param array $params
     *
     * @return bool
     */
    public function removeCart($params, $mode='')
    {
        if($mode=='fastbuy')
        {
            kernel::single('base_session')->start();
            $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
            unset( $_SESSION['cart_objects_fastbuy'][$userIdent] );
            kernel::single('base_session')->close();
            return true;
        }
        if( $params )
        {
            $filter['cart_id'] = $params['cart_id'];
        }
        $filter['user_ident'] = $this->objMdlCart->getUserIdentMd5($this->userId);

        $result = $this->objMdlCart->delete($filter);
        // app::get('systrade')->model('cart_item')->delete(array('cart_id'=>$params['cart_id']));

        $this->countCart();

        return $result;
    }

    /**
     * @brief 根据条件获取购物车信息
     *
     * @param array $filter
     *
     * @return array
     */
    public function getCartInfo($filter=array(), $needInvalid=true, $platform='pc')
    {
        $aCart = array();
        if(!$needInvalid) $filter['is_checked'] = '1';
        $cartData = $this->getBasicCart($filter);
        if( empty($cartData) ) return array();

        $result = $this->__preCartInfo($cartData, $needInvalid, $platform);

        $aCart['resultCartData'] = $result['resultCartData'];
        $aCart['totalCart'] = $result['totalCart'];
        if( empty($aCart['resultCartData']) ) $aCart = array();

        return $aCart;
    }

    /**
     * @brief 加载购物车显示数据结构
     *
     * @param array $data  加入购物车数据参数
     * @param array $shopNameArr 加入购物车商品的店铺名称集合
     * @param array $itemsData 加入购物车的基本商品数据集合
     * @param array $skusData 加入购物车的基本SKU数据集合
     *
     * @return array
     */
    private function __preCartInfo($cartData, $needInvalid, $platform)
    {
        $newCartData = [];
        $shopIds = [];
        foreach( $cartData as $row)
        {
            $shopIds[] = $row['shop_id'];
            $newCartData[$row['shop_id']][] = $row;
        }

        $shopIds = implode(',', $shopIds);
        $shopNameArr = app::get('systrade')->rpcCall('shop.get.list',array('shop_id'=>$shopIds,'fields'=>'shop_id,shop_type,shop_name'));
        $shopNameArr = array_bind_key($shopNameArr,'shop_id');

        foreach( $newCartData as $shopId=>$shopCartData )
        {
            //如果不存在则表示该店铺，已关闭，那么就不必要再查下该店铺的已加入购物车商品信息
            if( !$shopNameArr[$shopId] ) continue;
            $shopObjectData = $this->__preShopCartInfo($shopCartData, $needInvalid, $platform);
            if( $shopObjectData )
            {
                $resultCartData[$shopId]['shop_id'] = $shopId;
                $resultCartData[$shopId]['shop_name'] = $shopNameArr[$shopId]['shopname'];
                $resultCartData[$shopId]['shop_type'] = $shopNameArr[$shopId]['shop_type'];
                // 统计购物车的总数量，总价格，促销价格等综合信息
                $cartTotalInfo = $this->__cartTotal($shopObjectData, $shopId, $needInvalid, $platform);

                $resultCartData[$shopId]['cartCount'] = $cartTotalInfo['cartCount'];
                $resultCartData[$shopId]['basicPromotionListInfo'] = $cartTotalInfo['basicPromotionListInfo'];
                $resultCartData[$shopId]['usedCartPromotion'] = $cartTotalInfo['usedCartPromotion'];
                $resultCartData[$shopId]['usedCartPromotionWeight'] = $cartTotalInfo['usedCartPromotionWeight'];
                $resultCartData[$shopId]['cartByPromotion'] = $cartTotalInfo['cartByPromotion'];
                $resultCartData[$shopId]['cartByDlytmpl'] = $cartTotalInfo['cartByDlytmpl'];
                $resultCartData[$shopId]['object'] = $shopObjectData;
            }
        }
        $return['resultCartData'] = $resultCartData;

        // 统计购物车所有勾选商品的总重量，总数量，总价格，总促销价格
        $totalWeight   = 0;
        $totalNumber   = 0;
        $totalPrice    = 0;
        $totalDiscount = 0;
        foreach($resultCartData as $v)
        {
            $totalWeight   += $v['cartCount']['total_weight'];
            $totalNumber   += $v['cartCount']['itemnum'];
            $totalPrice    += $v['cartCount']['total_fee'];
            $totalDiscount += $v['cartCount']['total_discount'];
        }
        $return['totalCart'] = array(
            'totalWeight'        => $totalWeight,
            'number'             => $totalNumber,
            'totalPrice'         => $totalPrice,
            'totalAfterDiscount' => ecmath::number_minus( array($totalPrice, $totalDiscount) ),
            'totalDiscount'      => $totalDiscount,
        );

        return $return;
    }

    /**
     * 但店铺的购物车的信息统计，及应用促销规则
     * 购物车的cartid根据促销进行分组，索引为0的为不应用促销规则的分组
     * @param  array &$shopObjectData 购物车元数据
     * @return array 应用促销规则后的购物车统计信息
     */
    private function __cartTotal(&$shopObjectData, $shop_id, $needInvalid, $platform)
    {
        // 统一购物车内所有商品关联的促销唯一标识，方便通过促销排列购物车商品
        $basicPromotionListInfo = array();
        $cartByPromotion = array(); //购物车id按照购物车级别促销进行分组，索引为0的代表是不应用促销规则的购物车id分组
        foreach ($shopObjectData as $basicCartInfo)
        {
            if( $basicCartInfo['promotions'] )
            {
                foreach($basicCartInfo['promotions'] as $k => $v)
                {
                    $basicPromotionListInfo[$k] = $v;
                }
            }
            // 根据促销分类购物车数据(包括所有勾选及未勾选的，只要选择了促销规则的商品都分组)
            if( $needInvalid || $basicCartInfo['valid'])
            {
                $cartByPromotion[$basicCartInfo['selected_promotion']]['cart_ids'][] = $basicCartInfo['cart_id'];
            }
        }

        // 应用促销规则，只包括购物车勾选了商品及有效的商品才应用
        $shop_discount_fee = 0; //店铺总的优惠金额
        $shop_promotion_totalWeight = 0; //用于免邮，符合促销的商品的总重量
        $usedCartPromotion = array(); //使用的促销集合
        foreach($cartByPromotion as $kPomotionId=>&$vInfo)
        {
            // 应用满减
            if($basicPromotionListInfo[$kPomotionId]['promotion_type'] == 'fullminus')
            {
                $fullminus_dicount_price = $this->applyFullminus($shopObjectData, $vInfo['cart_ids'], $basicPromotionListInfo[$kPomotionId]);
                $shop_discount_fee += $fullminus_dicount_price;
                $fullminus_dicount_price ? $usedCartPromotion[] = $kPomotionId : null;
                $fullminus_dicount_price ? $vInfo['discount_price'] = $fullminus_dicount_price : 2;
            }
            // 应用满折
            if($basicPromotionListInfo[$kPomotionId]['promotion_type'] == 'fulldiscount')
            {
                $fulldiscount_dicount_price = $this->applyFulldiscount($shopObjectData, $vInfo['cart_ids'], $basicPromotionListInfo[$kPomotionId]);
                $shop_discount_fee += $fulldiscount_dicount_price;
                $fulldiscount_dicount_price ? $usedCartPromotion[] = $kPomotionId : null;
                $fulldiscount_dicount_price ? $vInfo['discount_price'] = $fulldiscount_dicount_price : 0;
            }
            // 应用xy折扣
            if($basicPromotionListInfo[$kPomotionId]['promotion_type'] == 'xydiscount')
            {
                $xydiscount_dicount_price = $this->applyXydiscount($shopObjectData, $vInfo['cart_ids'], $basicPromotionListInfo[$kPomotionId]);
                $shop_discount_fee += $xydiscount_dicount_price;
                $xydiscount_dicount_price ? $usedCartPromotion[] = $kPomotionId : null;
                $xydiscount_dicount_price ? $vInfo['discount_price'] = $xydiscount_dicount_price : 0;
            }
            // 应用免邮
            if($basicPromotionListInfo[$kPomotionId]['promotion_type'] == 'freepostage')
            {
                $forPromotionTotalWeight = $this->applyFreepostage($shopObjectData, $vInfo['cart_ids'], $basicPromotionListInfo[$kPomotionId]);
                $shop_promotion_totalWeight += $forPromotionTotalWeight;
            }
        }

        // 应用优惠券(结算页，购物车页不应用)
        if(!$needInvalid && ($couponCode = $this->getCouponCode($shop_id)))
        {
            $coupon_dicount_price = $this->applyCoupon($shopObjectData, $couponCode);
            $shop_discount_fee += $coupon_dicount_price;
        }

        $cartCount = array();
        $total_fee = $itemnum = $total_weight = 0;
        foreach ($shopObjectData as $k1=>$v1)
        {
            //统计购物车价格，数量，价格等
            if( $v1['valid'] && $v1['is_checked']=='1' )
            {
                $total_weight += $v1['weight'];
                $itemnum += $v1['quantity'];
                $total_fee += $v1['price']['total_price'];
            }
        }

        // 计算运费需要的信息
        $itemByDlytmpl = [];
        foreach($shopObjectData as $kCartid=>$vCartinfo)
        {
            if( $vCartinfo['valid'] && $vCartinfo['is_checked']=='1' )
            {
                $itemByDlytmpl = array_merge($itemByDlytmpl,$this->objects[$vCartinfo['obj_type']]->getInfoForPost($vCartinfo));
            }
        }
        $cartByDlytmpl = [];
        foreach($itemByDlytmpl as $v)
        {
            $cartByDlytmpl[$v['dlytmpl_id']]['total_quantity'] += $v['total_quantity'];
            $cartByDlytmpl[$v['dlytmpl_id']]['total_weight']    = ecmath::number_plus( array($v['total_weight'],$cartByDlytmpl[$v['dlytmpl_id']]['total_weight']));
            $cartByDlytmpl[$v['dlytmpl_id']]['total_price']     = ecmath::number_plus( array($v['total_price'],$cartByDlytmpl[$v['dlytmpl_id']]['total_price']));
        }

        $return['basicPromotionListInfo'] = $basicPromotionListInfo;
        $return['usedCartPromotion'] = $usedCartPromotion;
        $return['usedCartPromotionWeight'] = $shop_promotion_totalWeight;
        $return['cartByPromotion'] = $cartByPromotion;
        $return['cartByDlytmpl'] = $cartByDlytmpl;
        $return['cartCount'] = array(
            'total_weight' => $total_weight,
            'itemnum' => $itemnum,
            'total_fee' => $total_fee,
            'total_discount' => $shop_discount_fee,
            'total_coupon_discount' => $coupon_dicount_price,
        );

        return $return;

    }

    /**
     * @brief 加载每个店铺的商品数据信息
     *
     * @param array $shopCartData 店铺中加入购物车数据的
     * @param array $itemsData 加入购物车的基本商品数据集合
     * @param array $skusData 加入购物车的基本SKU数据集合
     *
     * @return array
     */
    private function __preShopCartInfo($shopCartData, $needInvalid, $platform)
    {
        $itemIds = $skuIds = [];
        foreach( $shopCartData as $row)
        {
            if($row['obj_type']=='item')
            {
                $itemIds[] = $row['item_id'];
                $skuIds[]  = $row['sku_id'];
            }
        }
        if($itemIds && $skuIds)
        {
            $itemRows = 'item_id,cat_id,title,weight,image_default_id,sub_stock,violation,disabled,dlytmpl_id';
            $skuRows = 'sku_id,bn,item_id,spec_info,price,weight,status';
            $itemFields['status'] = 'approve_status';
            $itemFields['promotion'] = 'promotion';

            $itemsData = $this->objLibItemInfo->getItemList($itemIds, $itemRows, $itemFields);
            $skusData = $this->objLibItemInfo->getSkusList($skuIds,$skuRows);
        }
        //现在只有普通商品购买流程，因此临时将商品结构写到此
        //如果有其他商品购买类型，则到各类型中进行商品获取
        foreach( $shopCartData as $row )
        {
            $k = $row['cart_id'];
            $shopObjectData[$k] = $this->objects[$row['obj_type']]->processCartObject($row,$itemsData,$skusData);

            $itemId = $row['item_id'];

            // 获取商品关联的促销信息
            if($row['obj_type']=='item')
            {
                $item_id=$row['item_id'];
                $shopObjectData[$k]['promotions'] = $this->getItemPromotionInfo($itemId, $platform);
                // 根据促销的创建时间进行倒序排序，则最新的默认选为商品的促销规则
                if($shopObjectData[$k]['promotions'])
                {
                    // 取商品对应的最后（最新）一个促销(已排序)，默认促销就用这个
                    $latestPromotion = end($shopObjectData[$k]['promotions']);
                    $priorityPromotionId = $latestPromotion['promotion_id'];
                    if($priorityPromotionId && !$row['selected_promotion'] && $row['selected_promotion']!=='0' )
                    {
                        $shopObjectData[$k]['selected_promotion'] = $priorityPromotionId;
                    }
                }

                //或商品享受的赠品信息
                $quantity = $row['quantity'];
                $shopObjectData[$k]['gift'] = $this->getItemGiftInfo($itemId,$quantity);
            }
            if( !$needInvalid && (!$shopObjectData[$k]['valid'] || !$row['is_checked']) )
            {
                unset($shopObjectData[$k]);
            }
        }

        return $shopObjectData;
    }

    /**
     * 根据商品返回其相关的促销信息
     * @param  int $itemId 商品id
     * @return array         促销信息数组
     */
    public function getItemPromotionInfo($itemId, $platform='pc')
    {
        $itemPromotionTagInfo = app::get('systrade')->rpcCall('item.promotion.get', array('item_id'=>$itemId),'buyer');
        if(!$itemPromotionTagInfo)
        {
            return false;
        }
        $allPromotion = array();
        foreach($itemPromotionTagInfo as $v)
        {
            $basicPromotionInfo = app::get('systrade')->rpcCall('promotion.promotion.get', array('promotion_id'=>$v['promotion_id'], 'platform'=>$platform), 'buyer');
            if($basicPromotionInfo['valid']===true)
            {
                $allPromotion[$v['promotion_id']] = $basicPromotionInfo;
            }
        }
        // 倒序排序，购物车的默认促销规则选择最新添加的促销适用
        ksort($allPromotion);
        return $allPromotion;
    }

    /**
     * 根据商品返回其单品促销活动，如团购价
     * @param  int $itemId 商品id
     * @return array         活动信息数组
     */
    public function getItemActivityInfo($itemId, $platform='pc')
    {
        $promotionDetail = app::get('topc')->rpcCall('promotion.activity.item.info',array('item_id'=>$itemId, 'platform'=>$platform, 'valid'=>1), 'buyer');
        if(!$promotionDetail)
        {
            return false;
        }
        return $promotionDetail;
    }

    /**
     * 应用满减促销
     * @param  array &$shopObjectData 购物车商品数据
     * @param  array $vCartIds        本促销对应购物车id
     * @param  array $promotionDetail 本促销的详情
     * @return float                  返回折扣金额
     */
    private function applyFullminus(&$shopObjectData, $vCartIds, $promotionDetail)
    {
        $forPromotionTotalPrice = 0; // 对应促销商品的总价
        foreach ($vCartIds as $cartId)
        {
            if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
            {
                $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $shopObjectData[$cartId]['price']['total_price']) );
            }
        }

        $applyData = array(
            'promotion_id' => $promotionDetail['promotion_id'],
            'fullminus_id' => $promotionDetail['rel_promotion_id'],
            'forPromotionTotalPrice' => $forPromotionTotalPrice,
        );
        $discount_price = app::get('systrade')->rpcCall('promotion.fullminus.apply', $applyData, 'buyer');
        if($discount_price>0)
        {
            // 优惠分摊
            foreach ($vCartIds as $cartId)
            {
                if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
                {
                    $percent = ecmath::number_div(array($shopObjectData[$cartId]['price']['total_price'], $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($discount_price, $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                    $shopObjectData[$cartId]['promotion_type'] = $promotionDetail['promotion_type'];
                    $shopObjectData[$cartId]['promotion_id'] = $promotionDetail['promotion_id'];
                }
            }
        }
        return $discount_price;
    }

    /**
     * 应用满折促销
     * @param  array &$shopObjectData 购物车商品数据
     * @param  array $vCartIds        本促销对应购物车id
     * @param  array $promotionDetail 本促销的详情
     * @return float                  返回折扣金额
     */
    private function applyFulldiscount(&$shopObjectData, $vCartIds, $promotionDetail)
    {
        $forPromotionTotalPrice = 0; // 对应促销商品的总价
        foreach ($vCartIds as $cartId)
        {
            if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
            {
                $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $shopObjectData[$cartId]['price']['total_price']) );
            }
        }

        $applyData = array(
            'promotion_id' => $promotionDetail['promotion_id'],
            'fulldiscount_id' => $promotionDetail['rel_promotion_id'],
            'forPromotionTotalPrice' => $forPromotionTotalPrice,

        );
        $discount_price = app::get('systrade')->rpcCall('promotion.fulldiscount.apply', $applyData, 'buyer');
        if($discount_price>0)
        {
            // 优惠分摊
            foreach ($vCartIds as $cartId)
            {
                if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
                {
                    $percent = ecmath::number_div(array($shopObjectData[$cartId]['price']['total_price'], $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($discount_price, $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                    $shopObjectData[$cartId]['promotion_type'] = $promotionDetail['promotion_type'];
                    $shopObjectData[$cartId]['promotion_id'] = $promotionDetail['promotion_id'];
                }
            }
        }
        return $discount_price;
    }

    /**
     * 应用XY折促销
     * @param  array &$shopObjectData 购物车商品数据
     * @param  array $vCartIds        本促销对应购物车id
     * @param  array $promotionDetail 本促销的详情
     * @return float                  返回折扣金额
     */
    private function applyXydiscount(&$shopObjectData, $vCartIds, $promotionDetail)
    {
        $forPromotionTotalPrice = 0; // 对应促销商品的总价
        $forPromotionTotalQuantity = 0; // 对应促销商品的总数量
        foreach ($vCartIds as $cartId)
        {
            if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
            {
                $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $shopObjectData[$cartId]['price']['total_price']) );
                $forPromotionTotalQuantity = ecmath::number_plus( array($forPromotionTotalQuantity, $shopObjectData[$cartId]['quantity']) );
            }
        }

        $applyData = array(
            'promotion_id' => $promotionDetail['promotion_id'],
            'xydiscount_id' => $promotionDetail['rel_promotion_id'],
            'forPromotionTotalPrice' => $forPromotionTotalPrice,
            'forPromotionTotalQuantity' => $forPromotionTotalQuantity,

        );
        $discount_price = app::get('systrade')->rpcCall('promotion.xydiscount.apply', $applyData, 'buyer');
        if($discount_price>0)
        {
            // 优惠分摊
            foreach ($vCartIds as $cartId)
            {
                if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
                {
                    $percent = ecmath::number_div(array($shopObjectData[$cartId]['price']['total_price'], $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($discount_price, $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                    $shopObjectData[$cartId]['promotion_type'] = $promotionDetail['promotion_type'];
                    $shopObjectData[$cartId]['promotion_id'] = $promotionDetail['promotion_id'];
                }
            }
        }
        return $discount_price;
    }

    /**
     * 应用免邮促销
     * @param  array &$shopObjectData 购物车商品数据
     * @param  array $vCartIds        本促销对应购物车id
     * @param  array $promotionDetail 本促销的详情
     * @return float                  返回应用免邮的商品的重量的和
     */
    private function applyFreepostage(&$shopObjectData, $vCartIds, $promotionDetail)
    {
        $forPromotionTotalPrice = 0; // 对应促销商品的总价
        $forPromotionTotalQuantity = 0; // 对应促销商品的总数量
        $forPromotionTotalWeight = 0; // 对应促销商品的总重量
        foreach ($vCartIds as $cartId)
        {
            if($shopObjectData[$cartId]['valid'] && $shopObjectData[$cartId]['is_checked']=='1')
            {
                $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $shopObjectData[$cartId]['price']['total_price']) );
                $forPromotionTotalQuantity = ecmath::number_plus( array($forPromotionTotalQuantity, $shopObjectData[$cartId]['quantity']) );
                $forPromotionTotalWeight = ecmath::number_plus( array($forPromotionTotalWeight, $shopObjectData[$cartId]['weight']) );
            }
        }

        $applyData = array(
            'promotion_id' => $promotionDetail['promotion_id'],
            'freepostage_id' => $promotionDetail['rel_promotion_id'],
            'forPromotionTotalPrice' => $forPromotionTotalPrice,
            'forPromotionTotalQuantity' => $forPromotionTotalQuantity,

        );
        $freePostageFlag = app::get('systrade')->rpcCall('promotion.freepostage.apply', $applyData, 'buyer');
        if($freePostageFlag)
        {
            return $forPromotionTotalWeight;
        }
        return 0;
    }

    /**
     * 应用免邮促销
     * @param  array $shopObjectData  购物车商品数据
     * @param  array $couponCode      优惠券码
     * @return float                  返回应用优惠券的金额
     */
    private function applyCoupon(&$shopObjectData, $couponCode)
    {
        $itemWithTotalPriceArr = array();
        foreach ($shopObjectData as $k1=>$v1)
        {
            //统计购物车价格，数量，价格等
            if( $v1['valid'] && $v1['is_checked']=='1' )
            {
                $itemWithTotalPriceArr[$k1] = $v1['item_id'].'_'.$v1['price']['total_price'];
            }
        }

        if( !$itemWithTotalPriceArr ) return 0;

        // 应用优惠券
        $apiParams = array(
            'coupon_code' => $couponCode,
            'cartItemsInfo' => implode('|', $itemWithTotalPriceArr),
        );

        $couponDiscountData = app::get('systrade')->rpcCall('promotion.coupon.apply', $apiParams,'buyer');
        if( $couponDiscountData['deduct_money'] )
        {
            //统计使用优惠券的商品总金额
            $forPromotionTotalPrice = 0;
            foreach( $itemWithTotalPriceArr as $cartId=>$itemsInfo )
            {
                $itemInfoArr = explode('_', $itemsInfo);
                $itemId = $itemInfoArr[0];
                if( in_array($itemId,$couponDiscountData['use_item_id']) )
                {
                    $forPromotionTotalPrice = ecmath::number_plus( array($forPromotionTotalPrice, $itemInfoArr[1]) );
                }
            }

            //优惠分摊
            foreach( $itemWithTotalPriceArr as $cartId=>$itemsInfo )
            {
                $itemInfoArr = explode('_', $itemsInfo);
                $itemId = $itemInfoArr[0];
                if( in_array($itemId,$couponDiscountData['use_item_id']) )
                {
                    $totalPrice = $itemInfoArr[1];
                    $percent = ecmath::number_div(array($totalPrice, $forPromotionTotalPrice) );
                    $divide_order_price = ecmath::number_multiple(array($couponDiscountData['deduct_money'], $percent) );
                    $shopObjectData[$cartId]['price']['discount_price'] = ecmath::number_plus( array( $shopObjectData[$cartId]['price']['discount_price'], $divide_order_price) );
                }
            }

            return $couponDiscountData['deduct_money'];
        }
        else
        {
            return 0;
        }
    }

    /**
     * @brief 根据条件查询到基本的购物车数据
     *
     * @param array $filter
     *
     * @return array
     */
    public function getBasicCart($filter)
    {
        if( empty($filter['mode']) || $filter['mode'] == 'cart' )
        {
            unset($filter['mode']);
            $params = $this->objMdlCart->getList('*', $filter);
        }
        else
        {
            $data = $this->fastBuyFetch();

            if( $data )
            {
                $params[0] = $data;
            }
        }
        return $params;
    }

    // 获取结算页使用的某店铺优惠券
    public function getCouponCode($shop_id)
    {
        $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
        return $_SESSION['cart_use_coupon'][$userIdent][$shop_id];
    }

    /**
     * @brief 选择的优惠券放入购物车优惠券表
     *
     * @param array $data
     *
     * @return array
     */
    public function addCouponCart($coupon_code, $shop_id)
    {
        kernel::single('base_session')->start();
        $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
        $_SESSION['cart_use_coupon'][$userIdent][$shop_id] = $coupon_code;
        kernel::single('base_session')->close();
        return true;

        // $objMdlCartCoupon = app::get('systrade')->model('cart_coupon');
        // $data = array(
        //     'coupon_id' => $coupon_id,
        //     'coupon_code' => $coupon_code,
        //     'user_id' => $user_id,
        //     'shop_id' => $shop_id,
        // );

        // return $objMdlCartCoupon->save($data);
    }

    /**
     * @brief 取消优惠券
     *
     * @param array $data
     *
     * @return array
     */
    public function cancelCouponCart($coupon_code, $shop_id)
    {
        if($coupon_code == '-1')
        {
            kernel::single('base_session')->start();
            $userIdent = $this->objMdlCart->getUserIdentMd5($this->userId);
            unset($_SESSION['cart_use_coupon'][$userIdent][$shop_id]);
            kernel::single('base_session')->close();
            return true;
            // $objMdlCartCoupon = app::get('systrade')->model('cart_coupon');
            // $data = array(
            //     'user_id' => "此处user_id需要重新传入",
            //     'shop_id' => $shop_id,
            // );
            // return $objMdlCartCoupon->delete($data);
        }
        else
        {
            return false;
        }

    }

    public function getItemGiftInfo($itemId,$quantity)
    {
        $giftItemInfo = app::get('topc')->rpcCall('promotion.gift.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');

        if($giftItemInfo && $quantity >= $giftItemInfo['limit_quantity'])
        {
            $skuIds = array_column($giftItemInfo['gift_item'],'sku_id');
            $skuRows = "sku_id";
            $skusData = $this->objLibItemInfo->getSkusList($skuIds,$skuRows);
            $quotient =floor($quantity/$giftItemInfo['limit_quantity']);
            foreach($giftItemInfo['gift_item'] as $key=>&$value)
            {
                if($value['approve_status'] == "instock")
                {
                    unset($giftItemInfo['gift_item'][$key]);
                    continue;
                }
                $value['gift_num'] = $value['gift_num']*$quotient;
                $value['realStore'] = $skusData[$value['sku_id']]['realStore'];
            }

            $valid_grade = explode(',', $giftItemInfo['valid_grade']);
            $gradeInfo = app::get('syspromotion')->rpcCall('user.grade.basicinfo');
            if( !in_array($gradeInfo['grade_id'], $valid_grade) )
            {
                return array();
            }

            return $giftItemInfo;
        }
        return array();
    }

}


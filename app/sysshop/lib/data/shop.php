<?php

/**
 * @brief 店铺相关
 */
class sysshop_data_shop {

    /**
     * 店铺类型列表数据
     */
    public $shopTypeData = null;

    /**
     * @brief 开通店铺
     *
     * @param $enterapplyId int
     *
     * @return  bool
     */
    public function openShop($enterapplyId)
    {
        $objMdlShop = app::get('sysshop')->model('shop');
        $objMdlSeller = app::get('sysshop')->model('seller');
        $objMdlSellerRel = app::get('sysshop')->model('shop_rel_seller');
        $objMdlShopInfo = app::get('sysshop')->model('shop_info');
        $objMdlShopCat = app::get('sysshop')->model('shop_rel_lv1cat');
        $objMdlShopBrand = app::get('sysshop')->model('shop_rel_brand');
        $objMdlEnterapply = app::get('sysshop')->model('enterapply');
        $params = $objMdlEnterapply->getRow("*",array('enterapply_id'=>$enterapplyId));

        if(!$params['seller_id'])
        {
            $msg = "店铺管理员不可为空";
            throw new \LogicException($msg);
            return false;
        }
        if(!$params['shop_name'])
        {
            $msg = "店铺名称不可为空";
            throw new \LogicException($msg);
            return false;
        }

        if($params['status'] == "finish")
        {
            $msg = "店铺已经开通，无需再次开通";
            throw new \LogicException($msg);
            return false;
        }

        $shop = unserialize($params['shop']);
        $shop['seller_id'] = $params['seller_id'];
        $shop['shop_name'] = $params['shop_name'];
        $shop['shop_type'] = $params['shop_type'];
        $shop['shopuser_name'] = $params['shopuser_name'];
        $shop['open_time'] = time();
        $shop['status'] = 'active';
        if(!$shop['shop_cat'])
        {
            $msg = "店铺签约类目不可为空";
            throw new \LogicException($msg);
            return false;
        }

        $db = app::get('sysshop')->database();
        $db->beginTransaction();
        try
        {
            $shopInfo = unserialize($params['shop_info']);
            if($shopInfo['corporate_identity_img_z'])
            {
                $shopInfo['corporate_identity_img']['z'] = $shopInfo['corporate_identity_img_z'];
            }
            if($shopInfo['corporate_identity_img_f'])
            {
                $shopInfo['corporate_identity_img']['f'] = $shopInfo['corporate_identity_img_f'];
            }

            if($shopInfo['shopuser_identity_img_z'])
            {
                $shop['shopuser_identity_img']['z'] = $shopInfo['shopuser_identity_img_z'];
            }
            if($shopInfo['shopuser_identity_img_f'])
            {
                $shop['shopuser_identity_img']['f'] = $shopInfo['shopuser_identity_img_f'];
            }
            if($shopInfo['brand_warranty'])
            {
                $brand_warranty = $shopInfo['brand_warranty'];
            }
            $shop_id = $objMdlShop->insert($shop);
            if(!$shop_id)
            {
                $msg = "店铺开通失败，shop信息保存出错";
                throw new \LogicException($msg);
            }

            $shopInfo['company_name'] = $params['company_name'];
            $shopInfo['seller_id'] = $params['seller_id'];
            $shopInfo['shop_id'] = $shop_id;
            $result = $objMdlShopInfo->insert($shopInfo);
            if(!$result)
            {
                $msg = "商店明细表插入失败";
                throw new \LogicException($msg);
            }

            if(is_array($shop['shop_cat']))
            {
                foreach($shop['shop_cat'] as $id)
                {
                    $shopcat = array(
                        'cat_id' => $id,
                        'shop_id'=>$shop_id,
                    );
                    $result[] = $objMdlShopCat->insert($shopcat);
                    if(!$result)
                    {
                        $msg = "商店关联类目表出错";
                        throw new \LogicException($msg);
                    }
                }
            }

            if(!in_array($shop['shop_type'],['cat','store'] ))
            {
                $shopbrand = array(
                    'brand_id'=>$shop['shop_brand'],
                    'shop_id'=>$shop_id,
                    'brand_warranty'=>$brand_warranty,
                );
                $result = $objMdlShopBrand->insert($shopbrand);
                if(!$result)
                {
                    $msg = "商店关联品牌表出错";
                    throw new \LogicException($msg);
                }
            }

            $sellerShop = array(
                'shop_id'=>$shop_id,
                'seller_id' => $params['seller_id'],
                'shop_name' => $params['shop_name'],
            );
            $result = $objMdlSellerRel->insert($sellerShop);
            if(!$result)
            {
                $msg = "商店关联商家表出错";
                throw new \LogicException($msg);
            }
            $logdata = unserialize($params['enterlog']);
            $los= array(
                array(
                    'plan'=>'开通店铺',
                    'times' => time(),
                    'hint' => '店铺已开通',
                    'status' => 'opened',
                ),
            );
            $losdata = array_merge($logdata,$los);
            $enterapplyData['enterapply_id'] = $enterapplyId;
            $enterapplyData['status'] = 'finish';
            $enterapplyData['enterlog'] = $losdata;
            $result = $objMdlEnterapply->save($enterapplyData);
            if(!$result)
            {
                $msg = "开通店铺log记录失败";
                throw new \LogicException($msg);
            }
            //修改seller表
            $seller = array(
                'shop_id'=>$shop_id,
                'seller_id' => $params['seller_id'],
            );
            $result = $objMdlSeller->save($seller);
            if(!$result)
            {
                $msg = "开通店铺失败";
                throw new \LogicException($msg);
            }
            $db->commit();
            return true;
        }
        catch(\LogicException $e)
        {
            $db->rollback();
            throw new \LogicException($e->getMessage());
            return false;
        }
    }

    /**
     * @brief 更改店铺状态
     *
     * @param $shopId 店铺编号
     * @param $status 更改的店铺状态
     *
     * @return  bool
     */
    public function updateShopStatus($shopdata)
    {
        if($shopdata['status'] == 'dead')
        {
            $shopdata['close_time'] = time();
        }
        $objMdlShop = app::get('sysshop')->model('shop');
        $result = $objMdlShop->save($shopdata);
        if(!$result)
        {
            $msg = "修改店铺状态失败";
            throw new \LogicException($msg);
            return false;
        }

        if($shopdata['status'] != "dead") return true;

        $objItem = kernel::single('sysitem_data_item');
        unset($shopdata['status'],$shopdata['close_time']);
        $result = $objItem->batchCloseItem($shopdata,'instock',$msg);
        if(!$result)
        {
            throw new \LogicException($msg);
            return false;
        }
        return true;
    }


    /**
     * @brief 更新店铺
     *
     * @param $shopData
     *
     * @return bool
     */
    public function saveShop($shopData)
    {

        $objMdlShop = app::get('sysshop')->model('shop');
        $shopName = $objMdlShop->getRow('shop_name',array('shop_name'=>$shopData['shop_name']));
        if($shopName['shop_name']&&empty($shopData['shop_id']))
        {
            $msg = "该店铺名称已经存在，请重新设置店铺名称.";
            throw new \LogicException($msg);
        }
        $result = $objMdlShop->save($shopData);
        return $result;
    }
    public function getShopById($shopId,$fields="*")
    {
        $objMdlShop = app::get('sysshop')->model('shop');
        $shopData = $objMdlShop->getRow($fields,array('shop_id'=>$shopId));
        return $shopData;
    }
    public function shopInfoUpdate($data,$shopId)
    {
        if(!$shopId)
        {
            $msg = "店铺Id不能为空！";
            throw new \LogicException($msg);
        }
        $objMdlShopInfo = app::get('sysshop')->model('shop_info');
        $objMdlShopInfo->update($data,array('shop_id'=>$shopId));
    }

    /**
     * @brief 根据条件查询店铺信息
     *
     * @param string $row 需要获取的字段
     * @param array  $filter 查询条件
     * @param bool  $isRow  是否为查询单条数据
     *
     * @return array
     */
    public function getShopInfo($row,$filter,$isRow=true)
    {
        $row =str_append($row,'shop_name,shop_type');
        $objMdlShop = app::get('sysshop')->model('shop');
        $objMdlShopType = app::get('sysshop')->model('shop_type');

        $shopData = $objMdlShop->getList($row.',shop_id',$filter);
        $this->shopTypeData = $this->shopTypeData ?: $objMdlShopType->getList('suffix,name,shop_type');
        $type = array_bind_key($this->shopTypeData,'shop_type');

        foreach( $shopData as $k=>&$rowData )
        {
            //为店铺名称填充后缀
            if($rowData['shop_name'] && $rowData['shop_type'])
            {
                $rowData['shopname'] = $rowData['shop_name'].$type[$rowData['shop_type']]['suffix'];
                $rowData['shoptype'] = $type[$rowData['shop_type']]['name'];
            }

            if( app::get('site')->getConf('site.subdomain_enabled') ){
                // 获取店铺子域名
                $rowData['subdomain'] = app::get('sysshop')->rpcCall('shop.subdomain.get',array('shop_id'=>$rowData['shop_id']))['subdomain'];
            }

            if( $isRow ) break;
        }

        return  $isRow ? $shopData[0] : $shopData;
    }

    /**
     * @brief 根据查询条件，获取多条店铺信息
     *
     * @param string $fields 需要返回的字段
     * @param array  $filter 查询条件
     * @param $offset
     * @param $limit
     *
     * @return array
     */
    public function fetchListShopInfo($fields="*", $filter, $offset=0, $limit=-1)
    {
        $objMdlShop = app::get('sysshop')->model('shop');
        $shopData = $objMdlShop->getList($fields,$filter,$offset,$limit);
        return $shopData;
    }

    /**
     * @brief 根据shopid获取 以及每个分类对应的佣金比例和入驻金
     *
     * @param shopid
     *
     * @return data
     */
    public function shopRelCatInfo($shopId)
    {
        $objMdlShopRelCat =  app::get('sysshop')->model('shop_rel_lv1cat');
        $catInfo = $objMdlShopRelCat->getList('*',array('shop_id'=>$shopId));
        return $catInfo;
    }

    public function getShopCatFee($shopCatInfo)
    {
        foreach ($shopCatInfo as $key => $value)
        {
            $shopCat[$key]['fee_confg'] = unserialize($shopCatInfo[$key]['fee_confg']);
            $shopCat[$key]['cat_id'] = $value['cat_id'];
            $shopCat[$key]['shop_id'] = $value['shop_id'];
        }
        foreach ($shopCat as $item => $fmt)
        {
            foreach ($fmt['fee_confg'] as $key => $value)
            {
                $lvName[$key] = $this->__getCatName($key);
                foreach ($value as $ke => $va)
                {
                    $lv2Name[$ke] = $this->__getCatName($ke);
                    foreach ($va as $k => $v)
                    {
                        $lv3Name[$k] = $this->__getCatName($k);
                    }
                }
            }

        }

        foreach ($shopCat as $item => $fmt)
        {
            foreach ($fmt['fee_confg'] as $key => $value)
            {
                $data[$key][$key]['cat_id'] = $key;
                $data[$key][$key]['cat_name'] = $lvName[$key];
                $data[$key][$key]['cat_fee'] = $value['lvfee'];
                unset($data[$key]['lvfee']);
                foreach ($value as $ke => $va)
                {
                    $data[$key][$ke][$ke]['cat_id'] = $ke;
                    $data[$key][$ke][$ke]['cat_name'] = $lv2Name[$ke];
                    $data[$key][$ke][$ke]['cat_fee'] = $va['lv2fee'];
                    unset($data[$key]['lvfee']);
                    foreach ($va as $k => $v)
                    {
                        $data[$key][$ke][$k]['cat_id'] = $k;
                        $data[$key][$ke][$k]['cat_name'] = $lv3Name[$k];
                        $data[$key][$ke][$k]['cat_fee'] = $v;
                        unset($data[$key][$ke]['lv2fee']);
                    }
                }
            }
        }
        return $data;
    }

    private function __getCatName($catId)
    {
        $data = app::get('sysshop')->rpcCall('category.cat.get.info',array('cat_id'=>$catId,'cat_name'));
        return $data[$catId]['cat_name'];
    }

    /**
     * @brief 根据shopid获取商店类目关联信息
     *
     * @param shopid
     *
     * @return data
     */
    public function getShopRelCat($shopId,$row="*")
    {
        $objMdlShopRelCat =  app::get('sysshop')->model('shop_rel_lv1cat');
        $objMdlCat =  app::get('syscategory')->model('cat');
        $catInfo = $objMdlShopRelCat->getList('cat_id',array('shop_id'=>$shopId));
        foreach($catInfo as $key=>$value)
        {
            $catIds['cat_id'][] = $value['cat_id'];
        }
        $cats = $objMdlCat->getList($row,$catIds);
        return $cats;
    }

    /**
     * @brief 根据cat_id获取店铺id
     *
     * @param array $catIds
     *
     * @return
     */
    public function getShopByCat($row,$filter, $offset=1, $limit=-1,$orderBy)
    {
        $objMdlShopRelCat =  app::get('sysshop')->model('shop_rel_lv1cat');

        $count = $objMdlShopRelCat->count($filter);
        //处理分页
        $pageTotal = ceil($count/$limit);
        $page =  $offset ? $offset : 1;
        $limit = $limit ? $limit : 40;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;


        $data['count'] = $count ;
        $data['list'] = $objMdlShopRelCat->getList($row, $filter, $offset, $limit,$orderBy);
        return $data;
    }

    /**
     * @brief 店铺关联的品牌(品牌旗舰店每个品牌仅此一家)
     *
     * @param $shopId 店铺编号
     *
     * @return array
     */
    public function getShopRelBrand($shopId,$row="*")
    {
        $filter['shop_id'] = $shopId;
        $objMdlShopRelBrand = app::get('sysshop')->model('shop_rel_brand');
        $objMdlBrand = app::get('syscategory')->model('brand');
        $relBrandIds = $objMdlShopRelBrand->getList('brand_id,brand_warranty',$filter);
        $brandIds = array();
        foreach($relBrandIds as $v)
        {
            $brandIds['brand_id'][] = $v['brand_id'];
            $brand[$v['brand_id']] = $v['brand_warranty'] ;
        }
        $result = $objMdlBrand->getList($row,$brandIds);
        foreach($result as $key=>$val)
        {
            $result[$key]['brand_warranty'] = $brand[$val['brand_id']];
        }
        return $result;

    }

    /**
     * @brief 根据brand_id获取店铺id
     *
     * @param array $brandIds
     *
     * @return
     */
    public function getShopByBrand($row,$filter, $offset=1, $limit=-1,$orderBy)
    {
        $objMdlShopRelBrand = app::get('sysshop')->model('shop_rel_brand');

        $count = $objMdlShopRelBrand->count($filter);
        //处理分页
        $pageTotal = ceil($count/$limit);
        $page =  $offset ? $offset : 1;
        $limit = $limit ? $limit : 40;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        $data['count'] = $count;
        $data['list'] = $objMdlShopRelBrand->getList($row, $filter, $offset, $limit,$orderBy);
        return $data;
    }


    /**
     * @brief 获取店铺详细信息（包含入驻的所有信息）
     *
     * @param $shopId
     * @param $row
     * @param $extends
     *
     * @return
     */
    public function getShopDetail($shopId,$row,$extends)
    {
        $shop = $this->getShopInfo($row,array('shop_id'=>$shopId));
        if($shopuser = $shop['shopuser_identity_img'])
        {
            if($shopuser = unserialize($shopuser))
            {
                $shop['shopuser_identity_img_z'] = $shopuser['z'];
                $shop['shopuser_identity_img_f'] = $shopuser['f'];
                unset($shop['shopuser_identity_img']);
            }
        }
        $shopData['shop'] = $shop;
        if(!$shopData['shop'])
        {
            return array();
        }
        if($extends['info'])
        {
            $row = $extends['info'];
            $objMdlShopInfo = app::get('sysshop')->model('shop_info');
            $shopInfo = $objMdlShopInfo->getRow($row,array('shop_id'=>$shopId));
            if($corporate = $shopInfo['corporate_identity_img'])
            {
                if($corporate = unserialize($corporate)){
                    $shopInfo['corporate_identity_img_z'] = $corporate['z'];
                    $shopInfo['corporate_identity_img_f'] = $corporate['f'];
                    unset($shopInfo['corporate_identity_img']);
                }
            }
            $shopData['shop_info'] = $shopInfo;
        }

        if($extends['cat'])
        {
            $row = $extends['cat'];
            $shopData['cat'] = $this->getShopRelCat($shopId,$row);
        }

        if($extends['brand'])
        {
            $row = $extends['brand'];
            // 获取类目店类型下所关联的所有品牌
            if($shop['shop_type'] == 'cat' || $shop['shop_type'] == 'store')
            {
                $shopData['brand'] = $this->__getCatBrand($shopId, $row);
            }
            else
            {
                $shopData['brand'] = $this->getShopRelBrand($shopId,$row);
            }
        }

        return $shopData;
    }

    public function checkShopName($params)
    {
        $filter['shop_name'] = $params['shop_name'];

        $filter['status'] = 'active';
        if($params['enterapply_id'])
        {
            $filter['enterapply_id|noequal'] = $params['enterapply_id'];
        }
        $objMdlEnterapply = app::get('sysshop')->model('enterapply');
        $enterCount = $objMdlEnterapply->count($filter);
        if($enterCount || intval($enterCount) >=1)
        {
            throw new LogicException('店铺名称'.$params['shop_name'].'正在入驻审核中，请核对并更换');
        }

        $shopfilter['shop_name'] = $params['shop_name'];
        if($params['shop_id'])
        {
            $shopfilter['shop_id|noequal'] = $params['shop_id'];
        }

        $objMdlShop = app::get('sysshop')->model('shop');
        $shopCount = $objMdlShop->count($shopfilter);
        if($shopCount || intval($shopCount) >=1)
        {
            throw new LogicException('店铺名称'.$params['shop_name'].'已被开通使用，请核对并更换');
        }

        return true;
    }

    private function __getCatBrand($shopId, $row)
    {
        $result = array();
        $cat = $this->getShopRelCat($shopId,'cat_id,parent_id');
        $catid = null;
        foreach ($cat as $v)
        {
            if($v['parent_id'] == 0)
            {
                $catid = $v['cat_id'];
                break;
            }
        }
        if($catid)
        {
            // 获取当前一级分类下的所有三级分类
            $catIds = app::get('sysshop')->rpcCall('category.cat.get.leafCatId', array('cat_id'=>$catid));
            if($catIds)
            {
                $catIds = explode(',', $catIds);
                $objMdlRelBrand = app::get('syscategory')->model('cat_rel_brand');
                $objMdlBrand = app::get('syscategory')->model('brand');
                $branIds = $objMdlRelBrand->getList('brand_id',array('cat_id'=>$catIds));
                if($branIds)
                {
                    $branIds = array_column($branIds, 'brand_id');
                    $result = $objMdlBrand->getList($row,array('brand_id' => $branIds));
                }
            }
        }

        return $result;
    }
}

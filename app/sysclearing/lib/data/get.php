<?php

/**
 * get.php 获取结算数据
 * Created Time 2016年3月14日 下午2:17:06
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysclearing_data_get {
    
    // 结算状态
    public $settlement_status = array (
            - 1 => '全部', 
            1 => '未结算', 
            2 => '已结算' 
    );
    // 结算类型
    public $settlement_type = array (
            - 1 => '全部', 
            '1' => '普通结算', 
            '2' => '运费结算', 
            '3' => '售后结算', 
            '4' => '拒收结算' 
    );

    /**
     * 获取结算汇总列表
     * 
     * @param array $filter
     * @param int $start
     * @param int $limit
     * @return bool|array
     * */
    public function getSettlementSummaryList($filter = array(), $start = 0, $limit = 20, $orderBy = '')
    {
        $fields = '*';
        if($filter['fields'])
        {
            $fields = $filter['fields'];
            unset($filter['fields']);
        }
        
        $objMdl = app::get ('sysclearing')->model ('settlement');
        $count = $objMdl->count ($filter);
        if (! $count)
        {return false;}
        $list = $objMdl->getList ($fields, $filter, $start, $limit, $orderBy);
        $this->__formatSummaryData ($list);
        
        $returnData = array (
                'count' => $count, 
                'list' => $list 
        );
        
        return $returnData;
    }

    /**
     * 获取结算明细列表
     * 
     * @param array $filter
     * @param int $start
     * @param int $limit
     * @return bool|array
     * */
    public function getSettlementDetailList($filter = array(), $start = 0, $limit = 20, $orderBy = '')
    {
        $fields = 'tid,shop_id,settlement_time,pay_time,item_id,bn,title,price,num,divide_order_fee,part_mjz_discount,payment,post_fee,refund_fee,cat_service_rate,commission_fee,settlement_fee,settlement_type,discount_fee';
        if($filter['fields'])
        {
            $fields = $filter['fields'];
            unset($filter['fields']);
        }
        
        $objMdl = app::get ('sysclearing')->model ('settlement_detail');
        $count = $objMdl->count ($filter);
        if (! $count)
        {return false;}
        
        $list = $objMdl->getList ($fields, $filter, $start, $limit, $orderBy);
        $this->__formatDetaiData ($list);
        
        $returnData = array (
                'count' => $count, 
                'list' => $list 
        );
        
        return $returnData;
    }

    /**
     * 根据条件获取商家列表
     * @param array $shopIds
     * @return array
     * */
    public function getShopList($shopIds = array())
    {

        if (! is_array ($shopIds))
        {return false;}
        
        $filter = array ();
        if ($shopIds)
        {
            $filter ['shop_id'] = $shopIds;
        }
        
        $objMdlShop = app::get ('sysshop')->model ('shop');
        $shopList = $objMdlShop->getList ('shop_id,shop_name', $filter);
        
        $shopTempIds = array_column ($shopList, 'shop_id');
        $shopTempName = array_column ($shopList, 'shop_name');
        $shopList = array_combine ($shopTempIds, $shopTempName);
        
        return $shopList;
    }

    /**
     * 格式化结算汇总数据
     * @param array $formtData
     * @return bool
     * */
    private function __formatSummaryData(&$formtData)
    {

        if (! $formtData || ! is_array ($formtData))
        {return false;}
        
        $data = array ();
        
        // 获取商家信息
        $shopIds = array_column ($formtData, 'shop_id');
        $shopList = $this->getShopList ($shopIds);
        
        // 开始处理数据
        foreach ($formtData as $value)
        {
            $value ['shop_name'] = $shopList [$value ['shop_id']];
            $value ['settlement_status_desc'] = $this->settlement_status [$value ['settlement_status']];
            $data [] = $value;
        }
        $formtData = $data;
        
        return true;
    }

    /**
     * 格式化结算明细数据
     * @param array $formtData
     * @return bool
     * */
    private function __formatDetaiData(&$formtData)
    {

        if (! $formtData || ! is_array ($formtData))
        {return false;}
        
        $data = array ();
        
        // 获取商家信息
        $shopIds = array_column ($formtData, 'shop_id');
        $shopList = $this->getShopList ($shopIds);
        
        // 开始处理数据
        foreach ($formtData as $value)
        {
            $value ['shop_name'] = $shopList [$value ['shop_id']];
            $value ['settlement_type_desc'] = $this->settlement_type [$value ['settlement_type']];
            $data [] = $value;
        }
        $formtData = $data;
        
        return true;
    }

}
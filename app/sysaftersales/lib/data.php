<?php

class sysaftersales_data {

    public $aftersalesSkuFields = 'oid,sku_id,bn,item_id,title,pic_path,spec_nature_info,price,payment,aftersales_status,complaints_status,points_fee,consume_point_fee';

    public function __construct()
    {
        $this->objMdlAftersales = app::get('sysaftersales')->model('aftersales');
    }

    /**
     * 检查子订单是否已经申请过退换货
     *
     * @param array $oid 子订单编号
     */
    public function verify($oids)
    {
        $filter['oid'] = $oids;
        $aftersalesList = $this->objMdlAftersales->getList('aftersales_bn,oid', $filter);
        $aftersalesData = array_bind_key($aftersalesList, 'oid');
        foreach( $oids as $oid )
        {
            $return[$oid] = $aftersalesData['oid'] ? true : false;
        }
        return $return;
    }

    /**
     * 获取单条售后数据
     *
     * @param array $fields 需要返回的约束字段
     * @param int $aftersalesBn 售后编号
     *
     * @return array 根据售后编号返回需要的数据，如果编号不存在则返回空数组
     */
    public function getAftersalesInfo($fields, $filter)
    {
        if( !$filter['aftersales_bn'] )
        {
            throw new \LogicException(app::get('sysaftersales')->_('售后编号不能为空'));
        }

        $aftersalesInfo = $this->objMdlAftersales->getRow($fields['rows'], $filter);
        if(!$aftersalesInfo)
        {
            throw new \LogicException(app::get('sysaftersales')->_('查询的售后单无效'));
            return false;
        }

        if(in_array($aftersalesInfo['status'],[1,2]) && in_array($aftersalesInfo['progress'],[8,7]))
        {
            $objMdlRefunds = app::get('sysaftersales')->model('refunds');
            $params = array(
                'aftersales_bn' => $aftersalesInfo['aftersales_bn'],
                'oid' => $aftersalesInfo['oid'],
                'refunds_type' =>0,
            );
            $refunds = $objMdlRefunds->getRow('status,total_price,refund_fee',$params);
            $aftersalesInfo['refunds'] = $refunds;
        }


        if( $aftersalesInfo && isset($fields['extends']['trade']) )
        {
            $tradeFiltr['tid'] = $aftersalesInfo['tid'];
            $tradeFiltr['oid'] = $aftersalesInfo['oid'];
            $tradeFiltr['fields'] = ($fields['extends']['trade'] != '*') ? $fields['extends']['trade'] : '*';
            $aftersalesSkuFields = ($fields['extends']['sku'] != '*') ? $fields['extends']['sku'] : explode(',',$this->aftersalesSkuFields);
            foreach( (array)$aftersalesSkuFields as $val)
            {
                $tradeFiltr['fields'] .= ',orders.'.$val;
            }
            $result = app::get('sysaftersales')->rpcCall('trade.get', $tradeFiltr);
            $aftersalesInfo['sku'] = $result['orders'][0];
            unset($result['orders']);
            $aftersalesInfo['trade'] = $result;
        }



        return $aftersalesInfo;
    }

    //根据子订单id获取该订单的售后
    public function ByOidgetAftersalesInfo($fields,$oid)
    {
        if(!$oid)
        {
            throw new \LogicException(app::get('sysaftersales')->_('子订单号为空'));
        }
        $aftersalesInfo = $this->objMdlAftersales->getRow($fields['rows'], array('oid'=>$oid));
        return $aftersalesInfo;
    }

    public function getAftersalesList($fields, $filter, $page=1, $limit, $orderBy="created_time desc")
    {
        if( empty($filter['user_id']) && empty($filter['shop_id']) )
        {
            throw new \LogicException(app::get('sysaftersales')->_('会员ID或者店铺ID必填一项'));
        }

        $pageData = $this->__preAftersalesListPage($filter, $page, $limit);
        if($pageData['pageTotal']<$page)
        {
            return array();
        }
        $aftersalesList = $this->objMdlAftersales->getList($fields['rows'], $filter, $pageData['offset'], $limit, $orderBy);
        if( empty($aftersalesList) ) return array();

        if( $aftersalesList && isset($fields['extends']['sku']) )
        {
            foreach( $aftersalesList as $value )
            {
                $tradeFiltr['oid'][] = $value['oid'];
            }
            $tradeFiltr['oids'] = implode(',',$tradeFiltr['oid']);
            $tradeFiltr['fields'] = ($fields['extends']['sku'] != '*') ? $fields['extends']['sku'] : $this->aftersalesSkuFields;
            $skuData = app::get('sysaftersales')->rpcCall('trade.order.list.get', $tradeFiltr);
            $skuData = array_bind_key($skuData,'oid');

            foreach( $aftersalesList as $k=>$row )
            {
                $aftersalesList[$k]['sku'] = $skuData[$row['oid']];
            }
        }

        $result['list'] = $aftersalesList;
        $result['total_found'] = $pageData['total_found'];
        return $result;
    }

    private function __preAftersalesListPage($filter, $pageNo=1, $pageSize)
    {
        $total = $this->objMdlAftersales->count($filter);
        $pageTotal = ceil($total/$pageSize);

        $page =  $pageNo ? $pageNo : 1;
        $limit = $pageSize ? $pageSize : 10;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $data['offset'] = ($currentPage-1) * $limit;
        $data['total_found'] = $total;
        $data['limit'] = $limit;
        $data['pageTotal'] = $pageTotal;
        return $data;
    }
}


<?php

/**
 * getOrderRefundList.php 
 * -- order.refund.list
 * -- 获取子订单退款信息列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class ectools_api_getOrderRefundList {
    public $apiDescription = '获取子订单退款信息列表';
    
    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
                'oids' => ['type'=>'string', 'valid'=>'required', 'title'=>'子订单号，多个订单使用英文逗号隔开', 'example'=>'160002,143301', 'desc'=>'子订单号，多个订单使用英文逗号隔开'],
                'fields' => ['type'=>'field_list', 'valid'=>'', 'title'=>'需要的字段', 'example'=>'', 'desc'=>'需要的字段'],
        );
    
        return $return;
    }
    
    /**
     * 获取子订单退款信息列表
     * @desc 获取子订单退款信息列表
     * @return string tid 订单号
     * @return string oid 子订单号
     * @return string aftersales_bn 售后编号
     * @return string rufund_type 退款类型
     */
    public function getList($params)
    {
        $filter = [];
        $filter['oid'] = explode(',', $params['oids']);
        $row = 'tid,oid,aftersales_bn,rufund_type';
        if($params['fields'])
        {
            $row = $row.','.$params['fields'];
            if(substr_count($row, '*'))
            {
                $row = '*';
            }
            else
            {
                $rowArr = explode(',', $row);
                $rowArr = array_unique($rowArr);
                $row = implode(',', $rowArr);
            }
        }
        
        $objMdlRefunds = app::get('ectools')->model('refunds');
        $refundsList = $objMdlRefunds->getList($row,$filter);
        
        return $refundsList;
    }
}
 
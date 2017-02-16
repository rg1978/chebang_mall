<?php

/**
 * - trade.payment.list
 * - 用于获取订单支付信息列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class ectools_api_getTradePaymentList {

    public $apiDescription = '获取订单支付信息列表';
    
    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
                'tids' => ['type'=>'string', 'valid'=>'required', 'title'=>'订单号，多个订单使用英文逗号隔开', 'example'=>'160002,143301', 'desc'=>'订单号，多个订单使用英文逗号隔开'],
                'fields' => ['type'=>'field_list', 'valid'=>'', 'title'=>'需要的字段', 'example'=>'', 'desc'=>'需要的字段'],
                'status' => ['type'=>'string', 'valid'=>'', 'title'=>'支付状态', 'example'=>'', 'desc'=>'支付状态'],
        );
    
        return $return;
    }
    
    /**
     * 获取订单支付信息列表
     * @desc 获取订单支付信息列表
     * @return string tid 订单号
     * @return string payment_id 支付单号
     * @return float money 支付金额
     * @return string status 支付状态
     * @return string pay_type 支付类型
     * @return string pay_app_id 支付方式名称
     */
    public function getList($params)
    {
        
        $filterBill = $filter = array();
        if($params['tids'])
        {
            $filterBill['tid'] = explode(',',$params['tids']);
        }
        if($params['status'])
        {
            $filterBill['status'] = $filter['status'] = $params['status'];
        }
        // 获取订单关联的支付单号
        if($filterBill)
        {
            $objMdlTradePaybill = app::get('ectools')->model('trade_paybill');
            $billList = $objMdlTradePaybill->getList('payment_id,tid',$filterBill);
            if($billList)
            {
                $filter['payment_id'] = array_column($billList,'payment_id');
            }
        }
        
        // 获取支付单信息
        if($filter)
        {
            $row = 'payment_id,money,status,pay_type,pay_app_id';
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
            
            $objMdlPayment = app::get('ectools')->model('payments');
            $paymentBill = $objMdlPayment->getList($row,$filter);
        }
        
        // 处理返回数据
        $result = array();
        if($paymentBill && $billList)
        {
            foreach($billList as $val)
            {
                // $paymentBill['trade'][$val['tid']] = $val;
                foreach ($paymentBill as $v)
                {
                    if($v['payment_id'] == $val['payment_id'])
                    {
                        $result[$val['tid']] = $v;
                    }
                }
            }
        }
        
        return $result;
    }
}


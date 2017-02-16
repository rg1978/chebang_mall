<?php
class sysclearing_finder_settlement_detail{

    public $column_pay_type = '订单支付方式';
    public $column_pay_type_order = 20;
    public $column_pay_type_width = 60;
    
    public function column_pay_type(&$colList, $list)
    {
        $tids = array_column($list, 'tid');
        $tids = array_unique($tids);
        $tids = implode(',', $tids);
        $params['tids'] = $tids;
        $params['fields'] = 'pay_name';
        $params['status'] = 'succ';
        $data = app::get('sysclearing')->rpcCall('trade.payment.list', $params);
        foreach($list as $k=>$row)
        {
            $colList[$k] = '--';
            if($row['settlement_fee']>=0)
            {
                $colList[$k] = $data[$row['tid']]['pay_name'];
            }
            
        }
    } 
    
    public $column_refund_type = '退款方式';
    public $column_refund_type_order = 30;
    public $column_refund_type_width = 60;
    
    public function column_refund_type(&$colList, $list)
    {
        $oids = array_column($list, 'oid');
        $oids = array_unique($oids);
        $oids = implode(',', $oids);
        $params['oids'] = $oids;
        $params['fields'] = 'rufund_type';
        $data = app::get('sysclearing')->rpcCall('order.refund.list', $params);
        $refundType = array (
                'online' => app::get('sysclearing')->_('在线退款'),
                'offline' => app::get('sysclearing')->_('线下退款'),
                'deposit' => app::get('sysclearing')->_('预存款退款'),
            );
        foreach($list as $k=>$row)
        {
            $colList[$k] = '--';
            foreach ($data as $val)
            {
                if($row['oid'] == $val['oid'] && $row['settlement_fee']<0)
                {
                    $colList[$k] = $refundType[$val['rufund_type']];
                }
            }
           
        }
    }
}


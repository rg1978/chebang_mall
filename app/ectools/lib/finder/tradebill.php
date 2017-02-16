<?php
class ectools_finder_tradebill{

    public $detail_basic = '详情';
    public function detail_basic($id)
    {
        $objMdlTradeBill = app::get('ectools')->model('trade_paybill');
        $row = $objMdlTradeBill->getRow('payment_id,tid',array('paybill_id'=>$id));
        $paymentId = $row['payment_id'];
        if($paymentId)
        {
            $objMdlPayments = app::get('ectools')->model('payments');
            $pagedata['detail'] = $objMdlPayments->getRow('status,pay_type,pay_app_id,pay_name,account,bank,pay_account,currency,paycost,pay_ver,ip,memo,trade_no,thirdparty_account,user_id,user_name,op_id,op_name',array('payment_id'=>$paymentId));
        }
        return view::make('ectools/pay_detail.html', $pagedata)->render();
    }
    
    public $column_pay_type = '支付方式';
    public function column_pay_type(&$colList, $list){
        $paymentIds = array_column($list, 'payment_id');
        $filter['payment_id'] = $paymentIds;
        $filter['status'] = 'succ';
        $objMdlPayments = app::get('ectools')->model('payments');
        $row = 'pay_name,payment_id';
        $result = $objMdlPayments->getList($row, $filter);
        foreach($list as $k=>$val)
        {
            $colList[$k] = '--';
            foreach ($result as $v)
            {
                if($val['payment_id'] == $v['payment_id'])
                {
                    $colList[$k] = $v['pay_name'];
                }
            }
        }
    }
}

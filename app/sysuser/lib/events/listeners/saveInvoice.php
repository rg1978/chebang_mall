<?php

class sysuser_events_listeners_saveInvoice {

    // 保存最近一次发票信息
    public function handle($data)
    {
        if($data['invoice']['need_invoice'])
        {
            $preInvoice = json_decode(redis::scene('sysuser')->hget('invoice_info', $data['user_id']), 1);
            if($data['invoice']['invoice_type']=='normal')
            {
                $data['invoice']['invoice_vat'] = $preInvoice['invoice_vat'];
            }
            redis::scene('sysuser')->hset('invoice_info', $data['user_id'], json_encode($data['invoice']));
        }
    }

}

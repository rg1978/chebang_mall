<?php

class sysaftersales_finder_aftersales{
    public $detail_basic = '基本信息';
    public function detail_basic($id)
    {
        $objMdlAftersales = app::get('sysaftersales')->model('aftersales');
        $aftersales = $objMdlAftersales->getRow('*',array('aftersales_bn'=>$id));
        $pagedata['trade'] = app::get('sysaftersales')->rpcCall('trade.order.get',array('tid'=>$aftersales['tid'],'oid'=>$aftersales['oid'],'fields' => 'tid,title,price,payment,num,sendnum,bn,status,pic_path,item_id'));
        //商家退款信息
        if(in_array($aftersales['progress'],['7','8']))
        {
            $refunds = app::get('topshop')->rpcCall('aftersales.refundapply.list.get',['fields'=>'status,total_price','oid'=>$aftersales['oid']]);
            $refunds = $refunds['list'][0];
            $pagedata['refunds'] = $refunds;
        }

        $aftersales['sendback_data'] = unserialize($aftersales['sendback_data']);
        $pagedata['data'] = $aftersales;
        return view::make('sysaftersales/admin/detail.html',$pagedata)->render();
    }
}

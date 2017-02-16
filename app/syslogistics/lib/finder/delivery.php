<?php
class syslogistics_finder_delivery{
    public $detail_basic = '基本信息';
    public function detail_basic($id)
    {
        $shippingType = array(
            'express' => '快递',
            'ziti' => '自提',
            'post' => '平邮',
            'ems' => 'EMS',
            'virtual' => '虚拟发货',
        );

        $objMdlDeliver = app::get('syslogistics')->model('delivery');
        $objMdlDeliverDetail = app::get('syslogistics')->model('delivery_detail');
        $delivery = $objMdlDeliver->getRow('*',array('delivery_id'=>$id));
        $params['tid'] = $delivery['tid'];
        $params['fields'] = 'shipping_type';
        $trade = app::get('syslogistics')->rpcCall('trade.get',$params);
        $delivery['shipping_type'] = $shippingType[$trade['shipping_type']];
        $pagedata['delivery'] = $delivery;
        $pagedata['detail'] = $objMdlDeliverDetail->getList('*',array('delivery_id'=>$id));
        return view::make('syslogistics/admin/delivery/detail.html', $pagedata)->render();
    }
}

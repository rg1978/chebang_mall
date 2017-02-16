<?php
class topshop_ctl_trade_cancel extends topshop_controller {

    public $limit = 10;

    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('订单取消列表');
        $apiParams['shop_id'] = $this->shopId;
        if( input::get('tid') )
        {
            $apiParams['tid'] = input::get('tid');
        }
        $apiParams['fields'] = '*';
        $apiParams['page_no']  = intval(input::get('pages',1));
        $apiParams['page_size'] = intval($this->limit);

        $data = app::get('topshop')->rpcCall('trade.cancel.list.get', $apiParams);

        if( $data['total'] )
        {
            $pagedata['list'] = $data['list'];
            $pagedata['count'] = $data['total'];
            $pagedata['pagers'] = $this->__pager($data['total'], input::get('pages',1));
        }
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        return $this->page('topshop/trade/cancel/list.html', $pagedata);
    }

    public function ajaxSearch()
    {
        switch( input::get('progress') )
        {
            case '0':
                $apiParams['refunds_status'] = 'WAIT_CHECK';
                break;
            case '1':
                $apiParams['refunds_status'] = 'WAIT_REFUND';
                break;
            case '2':
                $apiParams['refunds_status'] = 'SUCCESS';
                break;
            case '3':
                $apiParams['refunds_status'] = 'SHOP_CHECK_FAILS';
                break;
        }
        if( input::get('tid') )
        {
            $apiParams['tid'] = input::get('tid');
        }

        if( input::get('created_time') )
        {
            $times = array_filter(explode('-',input::get('created_time')));
            if($times)
            {
                $apiParams['created_time_start'] = strtotime($times['0']);
                $apiParams['created_time_end'] = strtotime($times['1'])+86400;
            }
        }

        $apiParams['shop_id'] = $this->shopId;
        $apiParams['fields'] = '*';
        $apiParams['page_no']  = intval(input::get('pages',1));
        $apiParams['page_size'] = intval($this->limit);

        try
        {
            $data = app::get('topshop')->rpcCall('trade.cancel.list.get', $apiParams);
        }
        catch( Exception $e)
        {
        }

        if( $data['total'] )
        {
            $pagedata['list'] = $data['list'];
            $pagedata['count'] = $data['total'];
            $pagedata['pagers'] = $this->__pager($data['total'], input::get('pages',1));
        }
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        return view::make('topshop/trade/cancel/item.html', $pagedata);
    }

    /**
     * 分页处理
     *
     * @param $total  总条数
     * @param $current 当前页
     */
    private function __pager($total, $current)
    {
        $filter = input::get();

        //处理翻页数据
        $current = $current ? $current : 1;

        $filter['pages'] = time();

        if( $total > 0 ) $totalPage = ceil($total/$this->limit);
        $current = $totalPage < $current ? $totalPage : $current;

        $pagers = array(
            'link'=>url::action('topshop_ctl_trade_cancel@ajaxSearch',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'use_app'=>'topshop',
            'token'=>time(),
        );

        return $pagers;
    }

    public function detail()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('订单取消详情');
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');

        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topshop_ctl_index@index'),'title' => app::get('topshop')->_('首页')],
            ['url'=> url::action('topshop_ctl_trade_cancel@index'),'title' => app::get('topshop')->_('订单取消列表')],
            ['title' => app::get('topshop')->_('订单取消详情')],
        );

        $cancelId = input::get('cancel_id');
        try{
            $data = app::get('topc')->rpcCall('trade.cancel.get',['shop_id'=>$this->shopId,'cancel_id'=>$cancelId]);
        }catch(Exception $e){
    	    return $this->page('topshop/trade/cancel/detail.html',$pagedata);
        }

        $pagedata['data'] = $data;

        //获取取消订单的订单数据
        $tid = $data['tid'];
        $params['tid'] = $tid;
        $params['fields'] = "user_id,tid,status,payment,points_fee,ziti_addr,ziti_memo,shipping_type,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,shop_id,orders.bn,cancel_reason,orders.refund_fee,orders.gift_data";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        $pagedata['trade'] = $tradeInfo;

        $userName = app::get('topshop')->rpcCall('user.get.account.name', ['user_id' => $tradeInfo['user_id']], 'seller');
        $pagedata['userName'] = $userName[$tradeInfo['user_id']];

        if($tradeInfo['shipping_type'] == 'ziti' )
        {
            $pagedata['ziti'] = "true";
        }

        if( $tradeInfo['status'] == 'WAIT_BUYER_CONFIRM_GOODS' || $tradeInfo['status'] == 'TRADE_FINISHED' )
        {
            $pagedata['logi'] = app::get('topshop')->rpcCall('delivery.get',array('tid'=>$tradeInfo['tid']));
        }
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

    	return $this->page('topshop/trade/cancel/detail.html',$pagedata);
    }

    //商家审核是否同意取消订单
    public function shopCheckCancel()
    {
        $params['cancel_id'] = input::get('cancel_id');
        $params['shop_id'] = $this->shopId;

        if( input::get('check_result','false') == 'false' )
        {
             $validator = validator::make(
                [ trim(input::get('shop_reject_reason'))],
                [ 'required|max:50'],
                ['拒绝理由必填|拒绝理由最多为50个字符!']
            );
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();

                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
            $params['status'] = 'reject';
            $params['reason'] = input::get('shop_reject_reason');
        }
        else
        {
            $params['status'] = 'agree';
        }

        try{
            app::get('topshop')->rpcCall('trade.cancel.shop.check',$params);
        }
        catch( LogicException $e ){
            return $this->splash('error',null, $e->getMessage(), true);
        }
        $this->sellerlog('处理订单取消申请。申请ID是'.$params['cancel_id']);
        $url = url::action('topshop_ctl_trade_cancel@detail',['cancel_id'=>$params['cancel_id']]);

        return $this->splash('success',$url, '审核提交成功', true);
    }
}


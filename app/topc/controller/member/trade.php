<?php
class topc_ctl_member_trade extends topc_ctl_member {

    public function tradeList()
    {
        $user_id = userAuth::id();
        $postdata = input::get();
        if(input::get('status'))
        {
            $status =input::get('status');
        }
        $params = array(
            'user_id' => userAuth::id(),
            'status' => $status,
            'page_no' =>intval($postdata['pages']) ? intval($postdata['pages']) : 1,
            'page_size' =>intval($this->limit),
            'order_by' =>'created_time desc',
            'fields' =>'order.spec_nature_info,tid,shop_id,user_id,status,cancel_status,payment,points_fee,total_fee,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.item_id,order.shop_id,order.status,order.spec_nature_info,activity,pay_type,order.sendnum,order.gift_data',
        );

        //如果执行了搜索
        if($postdata['keyword'])
        {
            $params['keyword'] = $postdata['keyword'];
            $tradelist = app::get('topc')->rpcCall('trade.order.list.item',$params);
        }
        else
        {
            $tradelist = app::get('topc')->rpcCall('trade.get.list',$params,'buyer');
        }

        $count = $tradelist['count'];
        $tradelist = $tradelist['list'];

        foreach( $tradelist as $key=>&$row)
        {
            $tradelist[$key]['is_buyer_rate'] = false;

            foreach( $row['order'] as $k=>$orderListData )
            {
                if( $row['buyer_rate'] == '0' && $row['status'] == 'TRADE_FINISHED' )
                {
                    $tradelist[$key]['is_buyer_rate'] = true;
                }

                if( isset($orderListData['aftersales_status']) && $orderListData['aftersales_status'] )
                {
                    $afterSelf = app::get('topc')->rpcCall('aftersales.get.bn',['oid'=>$orderListData['oid'],'fields'=>'aftersales_type']);
                    $tradelist[$key]['order'][$k]['aftersales_type'] = $afterSelf['aftersales_type'];
                }
                if($orderListData['gift_data'])
                {

                    $tradelist[$key]['gift_count'] += count($orderListData['gift_data']);
                }
            }
            // 获取店铺子域名
            $row['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$row['shop_id']))['subdomain'];
        }

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['trades'] = $tradelist;
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);
        $pagedata['count'] = $count;
        $pagedata['action'] = 'topc_ctl_member_trade@tradeList';
        $pagedata['imurl'] = app::get('topc')->res_full_url . '/im.png';
        $this->action_view = "trade/list.html";
        return $this->output($pagedata);
    }

    public function tradeDetail()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['fields'] = "tid,shipping_type,status,payment,points_fee,cancel_status,hongbao_fee,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,trade_memo,receiver_name,receiver_mobile,ziti_addr,ziti_memo,orders.price,orders.aftersales_status,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_vat_main,activity,cancel_reason,orders.spec_nature_info,orders.gift_data";
        $trade = app::get('topc')->rpcCall('trade.get',$params,'buyer');

        if( $trade['shipping_type'] == 'ziti' )
        {
            $pagedata['ziti'] = "true";
        }
        // 订单配送方式
        $shippingName = array(
                'express' => '快递',
                'ziti' => '自提',
                'post' => '平邮',
                'ems' => 'EMS',
                'virtual' => '虚拟发货',
            );
        $trade['shipping_type_name'] = $shippingName[$trade['shipping_type']];

        // 获取店铺子域名
        $trade['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$trade['shop_id']))['subdomain'];

        $pagedata['trade'] = $trade;
        $pagedata['logi'] = app::get('topc')->rpcCall('delivery.get',array('tid'=>$params['tid']));

        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['imurl'] = app::get('topc')->res_full_url . '/im.png';
        $pagedata['action'] = 'topc_ctl_member_trade@tradeList';
        $this->action_view = "trade/detail.html";
        return $this->output($pagedata);
    }

    public function ajaxGetTrack()
    {
        $postData = input::get();
        $pagedata['track'] = app::get('topc')->rpcCall('logistics.tracking.get.hqepay',$postData);
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        return view::make('topc/member/trade/logistics.html', $pagedata);
    }


    public function ajaxCancelTrade()
    {
        $validator = validator::make([input::get('tid')],['numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！');
        }
        $pagedata['tid'] = input::get('tid');
        $pagedata['reason'] = config::get('tradeCancelReason');
        return view::make('topc/member/gather/cancel.html', $pagedata);
    }

    public function ajaxConfirmTrade()
    {
        $validator = validator::make([input::get('tid')],['numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！');
        }
        $pagedata['tid'] = input::get('tid');
        return view::make('topc/member/gather/confirm.html', $pagedata);
    }

    public function cancelOrderBuyer()
    {
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');
        $validator = validator::make([$reasonPost],['required'],['取消原因必选!']);
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        if($reasonPost == "other")
        {
            $cancelReason = input::get('other_reason');
            $validator = validator::make([trim($cancelReason)],['required|max:50'],['取消原因必须填写!|取消原因最多填写50个字']);
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
        }
        else
        {
            $cancelReason = $reasonSetting['user'][$reasonPost];
        }

        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['cancel_reason'] = $cancelReason;
        try
        {
            app::get('topc')->rpcCall('trade.cancel.create',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topc_ctl_member_trade@tradeList');
        $msg = app::get('topc')->_('订单取消成功');

        // 获取订单状态
        $tparams['tid'] = input::get('tid');
        $tparams['user_id'] = userAuth::id();
        $tparams['fields'] = "tid,status";
        $trade = app::get('topc')->rpcCall('trade.get',$tparams,'buyer');
        if($trade['status'] !='WAIT_BUYER_PAY')
        {
            $msg = app::get('topc')->_('申请取消成功');
        }

        return $this->splash('success',$url,$msg,true);
    }

    public function confirmReceipt()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        try
        {
            app::get('topc')->rpcCall('trade.confirm',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        if($oid = input::get('oid',0))
        {
            $url = url::action('topc_ctl_member_aftersales@aftersalesApply',['tid' => $params['tid'],'oid' => $oid]);
        }
        else
        {
            $url = url::action('topc_ctl_member_trade@tradeList');
        }
        $msg = app::get('topc')->_('订单确认收货完成');
        return $this->splash('success',$url,$msg,true);
    }

    /**
     * 分页处理
     * @param int $current 当前页
     *
     * @return $pagers
     */
    private function __pages($current,$filter,$count)
    {
        //处理翻页数据
        $current = $current ? $current : 1;
        $filter['pages'] = time();
        $limit = $this->limit;

        if( $count > 0 ) $totalPage = ceil($count/$limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_member_trade@tradeList',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

    public function canceledTradeList()
    {
        $apiParams['page_no']  = intval(input::get('pages',1));
        if( input::get('tid') )
        {
            $apiParams['tid'] = input::get('tid');
        }
        $apiParams['page_size'] = intval($this->limit);
        $apiParams['fields'] = '*';
        $apiParams['user_id'] = userAuth::id();
        $data = app::get(topc)->rpcCall('trade.cancel.list.get', $apiParams);

        if( $data['total'] )
        {
            $filter = input::get();
            $pagedata['list'] = $data['list'];
            foreach($pagedata['list'] as $key=>$value)
            {
                foreach($value['order'] as $val)
                {
                    if($val['gift_data'])
                    {

                        $pagedata['list'][$key]['gift_count'] += count($val['gift_data']);
                    }
                }
            }


            //处理翻页数据
            $current = input::get('pages',1);
            $filter['pages'] = time();
            $limit = $this->limit;

            if( $data['total'] > 0 ) $totalPage = ceil($data['total']/$limit);
            $pagers = array(
                'link'=>url::action('topc_ctl_member_trade@canceledTradeList',$filter),
                'current'=>$current,
                'total'=>$totalPage,
                'token'=>time(),
            );
        }
        $pagedata['pagers'] = $pagers;
        $pagedata['action'] = 'topc_ctl_member_trade@canceledTradeList';
        $this->action_view = "trade/canceled.html";
        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');
        return $this->output($pagedata);
    }

    public function canceledTradeDetail()
    {
        $cancelId = input::get('cancel_id');
        $data = app::get('topc')->rpcCall('trade.cancel.get',['user_id'=>userAuth::id(),'cancel_id'=>$cancelId]);
        $pagedata['data'] = $data;
        $pagedata['action'] = 'topc_ctl_member_trade@canceledTradeList';
        $this->action_view = "trade/canceled_detail.html";
        return $this->output($pagedata);
    }
    public function ajaxHint()
    {
        $validator = validator::make([input::get('tid'),input::get('oid')],['numeric','numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！');
        }
        $pagedata = input::get();
        return view::make('topc/member/gather/hint.html', $pagedata);

    }

}

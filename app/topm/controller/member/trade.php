<?php
class topm_ctl_member_trade extends topm_ctl_member{

    public $tradeStatus = array(
        '1' => 'WAIT_BUYER_PAY',
        '2' => 'WAIT_SELLER_SEND_GOODS',
        '3' => 'WAIT_BUYER_CONFIRM_GOODS',
        '4' => 'TRADE_FINISHED',
    );

    /**
     * @brief 会员中心订单列表
     *
     * @return
     */
    public function index()
    {
        $filter = input::get();

        $pagedata = $this->__getTrade($filter);
        //echo '<pre>';print_r($pagedata);exit();
        if(empty($pagedata['trades']))
        {
            return $this->page('topm/member/trade/emptylist.html', $pagedata);
        }
        return $this->page('topm/member/trade/list.html', $pagedata);
    }

    public function tradeList()
    {
        $filter = input::get();
        //echo '<pre>';print_r($filter);exit();
        $pagedata = $this->__getTrade($filter);
        return $this->page('topm/member/trade/list.html', $pagedata);
    }

    /**
     * @brief 订单详情
     *
     * @return
     */
    public function detail()
    {
        $this->setLayoutFlag('order_detail');
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['fields'] = "tid,status,shop_id,user_id,shipping_type,payment,points_fee,post_fee,cancel_status,receiver_state,receiver_city,receiver_district,ziti_addr,ziti_memo,receiver_address,trade_memo,receiver_name,receiver_mobile,orders.oid,orders.price,orders.num,orders.title,orders.aftersales_status,orders.complaints_status,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.spec_nature_info,activity,pay_type,cancel_reason,orders.spec_nature_info";
        $trade = app::get('topm')->rpcCall('trade.get',$params,'buyer');
        if($trade['shipping_type'] == 'ziti')
        {
            $pagedata['ziti'] = "true";
        }

        $trade['is_buyer_rate'] = false;
        foreach( $trade['orders'] as $orderListData )
        {
            if( $trade['buyer_rate'] == '0' && $trade['status'] == 'TRADE_FINISHED' )
            {
                $trade['is_buyer_rate'] = true;
                break;
            }
        }
        $pagedata['trade'] = $trade;
        //获取发货信息
        $pagedata['logi'] = app::get('topm')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $pagedata['title'] = "订单详情";  //标题
        $pagedata['point_rate'] = app::get('topm')->rpcCall('point.setting.get',['field'=>'point.deduction.rate']);
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        return $this->page('topm/member/trade/detail.html', $pagedata);
    }

    public function ajaxGetTrack()
    {
        $postData = input::get();
        $pagedata['track'] = app::get('topm')->rpcCall('logistics.tracking.get.hqepay',$postData);
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        return view::make('topm/member/trade/logistics.html', $pagedata);
    }

    /**
     * @brief 订单取消页面
     *
     * @return
     */
    public function cancel()
    {
        $this->setLayoutFlag('order_detail');
        $validator = validator::make([input::get('tid')],['numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！',true);
        }
        $pagedata['tid'] = $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['fields'] = "status,post_fee,payment,points_fee,pay_type";
        $pagedata['trade'] = app::get('topm')->rpcCall('trade.get',$params);
        $pagedata['reason'] = config::get('tradeCancelReason');
        $pagedata['title'] = "取消订单";  //标题
        return $this->page('topm/member/trade/status/cancel.html', $pagedata);

    }

    /**
     * @brief 订单完成页面
     *
     * @return
     */
    public function confirm()
    {
        $this->setLayoutFlag('order_detail');
        $validator = validator::make([input::get('tid')],['numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！',true);
        }
        $pagedata['tid'] = $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['fields'] = "status,post_fee,payment,points_fee";
        $pagedata['trade'] = app::get('topm')->rpcCall('trade.get',$params);
        return $this->page('topm/member/trade/status/confirm.html', $pagedata);

    }

    /**
     * @brief 订单完成操作
     *
     * @return
     */
    public function confirmReceipt()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        try
        {
            app::get('topm')->rpcCall('trade.confirm',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topm_ctl_member_trade@index');
        $msg = app::get('topm')->_('订单确认收货完成');
        return $this->splash('success',$url,$msg,true);
    }


    /**
     * @brief 订单取消操作
     *
     * @return
     */
    public function cancelBuyer()
    {
        $this->setLayoutFlag('cart');
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');
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
            app::get('topm')->rpcCall('trade.cancel.create',$params);
        }
        catch(Exception $e)
        {
            $pagedata['msg'] = $e->getMessage();
            //return $this->splash('error',null,$msg,true);
            $pagedata['cancelerror'] = true;
        }
        $pagedata['url'] = url::action('topm_ctl_member_trade@index');
        // app::get('topm')->_('订单取消成功');
        return $this->page('topm/member/trade/status/result.html', $pagedata);
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
        $current = ($current && $current <= 100 ) ? $current : 1;

        if( $count > 0 ) $totalPage = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topm_ctl_member_trade@ajaxTradeShow',$filter),
            'current'=>intval($current),
            'total'=>intval($totalPage),
        );
        return $pagers;
    }

    public function ajaxTradeShow()
    {
        $postdata = input::get();
        $pagedata = $this->__getTrade($postdata);
        $data['html'] = view::make('topm/member/trade/listitem.html',$pagedata)->render();
        $data['pagers'] = $pagedata['pagers'];
        $data['success'] = true;
        return response::json($data);exit;
    }

    private function __getTrade($postdata)
    {

        if(isset($this->tradeStatus[$postdata['s']]))
        {
            if($postdata['s'] == 4)
            {
                $status = 'TRADE_FINISHED';
                $params['buyer_rate'] = 0;
            }
            else
            {
                $status =$this->tradeStatus[$postdata['s']];
            }
        }
        else
        {
            $postdata['s'] = 0;
        }
        $params['status'] = $status;
        $params['user_id'] = userAuth::id();
        $params['page_no'] = intval($postdata['pages']) ? intval($postdata['pages']) : 1;
        $params['page_size'] = intval($this->limit);
        $params['order_by'] = 'created_time desc';
        $params['fields'] = 'tid,shop_id,user_id,status,payment,points_fee,total_fee,cancel_status,itemnum,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.item_id,order.shop_id,order.status,activity,pay_type,order.spec_nature_info';

        $tradelist = app::get('topm')->rpcCall('trade.get.list',$params);
        $count = $tradelist['count'];
        $tradelist = $tradelist['list'];

        foreach( $tradelist as $key=>$row)
        {
            $tradelist[$key]['is_buyer_rate'] = false;

            foreach( $row['order'] as $orderListData )
            {
                if( $row['buyer_rate'] == '0' && $row['status'] == 'TRADE_FINISHED' )
                {
                    $tradelist[$key]['is_buyer_rate'] = true;
                    break;
                }
            }

            unset($tradelist[$key]['order']);
            $tradelist[$key]['order'][0] = $row['order'];

            if( !$tradelist[$key]['is_buyer_rate'] && $postdata['s'] == 4 )
            {
                unset($tradelist[$key]);
            }
        }

        $pagedata['trades'] = $tradelist;
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);
        $pagedata['count'] = $count;
        $pagedata['title'] = "我的订单";  //标题
        $pagedata['status'] =$postdata['s'];  //状态
        return $pagedata;

    }

    public function canceledTradeList()
    {
        $pagedata['title'] = "取消订单列表";  //标题
        $postdata = input::get();
        if(isset($postdata['s']) && $postdata['s'] )
        {
            if($postdata['s'] == 4)
            {
                $status = 'TRADE_FINISHED';
                $params['buyer_rate'] = 0;
            }
            else
            {
                $status =$this->tradeStatus[$postdata['s']];
            }
        }
        else
        {
            $postdata['s'] = 0;
        }
        $params['status'] = $status;
        $params['user_id'] = userAuth::id();
        $params['page_no'] = intval($postdata['pages']) ? intval($postdata['pages']) : 1;
        $params['page_size'] = intval($this->limit);
        $params['fields'] = "*";
        $params['order_by'] = 'created_time desc';

        $data = app::get(topm)->rpcCall('trade.cancel.list.get', $params);
        $count = $data['total'];
        $pagedata['list'] = $data['list'];

        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);
        $pagedata['status'] =$postdata['s'];  //状态
        $pagedata['count'] = $count;
        return $this->page('topm/member/trade/canceled.html', $pagedata);
    }

    public function canceledTradeDetail()
    {
        $pagedata['title'] = "取消订单详情";  //标题
        $cancelId = input::get('cancel_id');
        $data = app::get('topm')->rpcCall('trade.cancel.get',['user_id'=>userAuth::id(),'cancel_id'=>$cancelId]);
        $pagedata['data'] = $data;
        return $this->page('topm/member/trade/canceled_detail.html',$pagedata);
    }

    public function cancelOrderBuyer()
    {
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');
        $validator = validator::make($reasonPost,['required'],['取消原因必选!']);
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
            $validator = validator::make($cancelReason,['required'],['取消原因必须填写!']);
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
            app::get('topm')->rpcCall('trade.cancel.create',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
    }
}

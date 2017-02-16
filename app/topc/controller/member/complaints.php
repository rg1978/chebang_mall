<?php
class topc_ctl_member_complaints extends topc_ctl_member {

    /*
     * 显示订单投诉页面
     */
    public function complaintsView()
    {
        $oid = input::get('oid');
        //物流单号判断
        $validator = validator::make(
            [$oid],
            ['numeric']
        );
        if ($validator->fails())
        {
            return $this->splash('error',null,'格式不对!');
        }
        $pagedata['oid'] = $oid;
        $this->action_view = "complaints/view.html";
        return $this->output($pagedata);
    }

    /**
     * 提交订单投诉
     */
    public function complaintsCi()
    {
        try
        {
            $data = input::get();
            $validator = validator::make(
                [$data['complaints_type'],$data['tel'],$data['content']],
                ['required','required|mobile','required|min:5|max:300'],
                ['投诉类型不能为空!','联系方式不能为空!|联系方式格式不对','问题描述不能为空!|问题描述不能小于5字符|问题描述不能大于300字符']
            );
            $validator->newFails();

            $data['image_url'] = implode(',', $data['image_url']);
            $result = app::get('topc')->rpcCall('trade.order.complaints.create', $data,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topc_ctl_member_trade@tradeList');

        $msg = '投诉提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function detail()
    {
        $data['oid'] = input::get('oid');
        $data['complaints_id'] = input::get('complaintsid');
        $data['fields'] = 'complaints_id,shop_id,tid,oid,status,tel,image_url,complaints_type,content,memo,buyer_close_reasons,created_time,orders.title,orders.item_id';
        try
        {
            $pagedata = app::get('topc')->rpcCall('trade.order.complaints.info', $data,'buyer');
        }
        catch( LogicException $e)
        {
            $msg = $e->getMessage();
        }

        if( $pagedata['image_url'] )
        {
            $pagedata['image_url'] = explode(',',$pagedata['image_url']);
        }

        $this->action_view = "complaints/detail.html";
        return $this->output($pagedata);
    }

    public function closeComplaints()
    {

        $data['complaints_id'] = input::get('complaints_id');
        $data['buyer_close_reasons'] = input::get('buyer_close_reasons');
        $data['user_id'] = userAuth::id();

        $oid = input::get('oid');

        try
        {
            $pagedata = app::get('topc')->rpcCall('trade.order.complaints.buyer.close', $data);
        }
        catch( LogicException $e )
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        $url = url::action('topc_ctl_member_complaints@detail',['oid'=>$oid]);
        $msg = '订单投诉撤销成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function complaintsList()
    {
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 10;
        $params = array(
                'page_no' => intval($filter['pages']),
                'page_size' => intval($pageSize),
                'fields' =>'complaints_id,oid,complaints_type,tid,status,created_time',
                'user_id'=>userAuth::id(),
        );

        $complaintsListData = app::get('topc')->rpcCall('trade.order.complaints.list', $params);

        $count = $complaintsListData['count'];
        $complaintsList = $complaintsListData['list'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
                'link'=>url::action('topc_ctl_member_complaints@complaintsList',$filter),
                'current'=>$current,
                'total'=>$total,
                'token'=>$filter['pages'],
        );
        $pagedata['complaintsList']= $complaintsList;
        $pagedata['count'] = $count;
        $pagedata['action'] = 'topc_ctl_member_complaints@complaintsList';


        $this->action_view = "complaints/list.html";
        return $this->output($pagedata);
    }
}


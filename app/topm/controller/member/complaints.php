<?php
class topm_ctl_member_complaints extends topm_ctl_member {

    public $complaintsType = [
        '商品问题','配送问题','支付问题','促销活动问题','账户问题','发票问题',
        '系统问题','退货/换货问题','表扬/投诉工作人员','其他'
    ];

    /*
     * 显示订单投诉页面
     */
    public function complaintsView()
    {
        $this->setLayoutFlag('product');

        $oid = input::get('oid');
        $pagedata['oid'] = $oid;
        $pagedata['complaintsType'] = $this->complaintsType;

        $pagedata['title'] = "订单投诉";
        return $this->page('topm/member/complaints/view.html', $pagedata);
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
                    ['required','required|mobile','required|max:300'],
                    ['投诉类型不能为空!','联系方式不能为空!|联系方式格式不对','问题描述不能为空!|问题描述不能大于300字符']
            );
            $validator->newFails();
            $result = app::get('topm')->rpcCall('trade.order.complaints.create', $data,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topm_ctl_member_complaints@detail',['oid'=>input::get('oid')]);
        $msg = '投诉提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function detail()
    {
        $this->setLayoutFlag('product');

        $data['oid'] = input::get('oid');
        $data['fields'] = 'complaints_id,shop_id,tid,oid,status,tel,image_url,complaints_type,content,memo,buyer_close_reasons,created_time';
        try
        {
            $pagedata = app::get('topm')->rpcCall('trade.order.complaints.info', $data,'buyer');
        }
        catch( LogicException $e)
        {
            $msg = $e->getMessage();
        }

        if( $pagedata['image_url'] )
        {
            $pagedata['image_url'] = explode(',',$pagedata['image_url']);
        }

        $pagedata['title'] = "投诉详情";
        return $this->page('topm/member/complaints/detail.html', $pagedata);
    }

    public function closeComplaints()
    {
        $data['complaints_id'] = input::get('complaints_id');
        $data['buyer_close_reasons'] = input::get('buyer_close_reasons');
        $data['user_id'] = userAuth::id();

        $oid = input::get('oid');

        try
        {
            $validator = validator::make(
                    [$data['buyer_close_reasons']],
                    ['required|min:5|max:200'],
                    ['撤销原因必填|撤销原因在5-200字|撤销原因在5-200字']
            );
            $validator->newFails();
            $pagedata = app::get('topm')->rpcCall('trade.order.complaints.buyer.close', $data);
        }
        catch( LogicException $e )
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        $url = url::action('topm_ctl_member_trade@index');
        $msg = '订单撤销成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function closeView()
    {
        $this->setLayoutFlag('product');

        $pagedata['title'] = "撤销投诉";

        $data['oid'] = input::get('oid');
        $data['fields'] = 'complaints_id,oid,status,tel,image_url,complaints_type,content,memo,buyer_close_reasons,created_time';
        try
        {
            $pagedata = app::get('topm')->rpcCall('trade.order.complaints.info', $data,'buyer');
        }
        catch( LogicException $e)
        {
            $msg = $e->getMessage();
        }

        if( $pagedata['image_url'] )
        {
            $pagedata['image_url'] = explode(',',$pagedata['image_url']);
        }

        return $this->page('topm/member/complaints/close.html', $pagedata);
    }
}

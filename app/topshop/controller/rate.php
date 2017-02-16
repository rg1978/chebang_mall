<?php
class topshop_ctl_rate extends topshop_controller {

    public $limit = 10;

    //评价列表
    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('评价管理-评价列表');
        $pagedata = $this->__searchRateData();
        return $this->page('topshop/rate/list.html', $pagedata);
    }

    public function search()
    {
        $pagedata = $this->__searchRateData();
        return view::make('topshop/rate/list_item.html', $pagedata);
    }

    //评价搜索
    private function __searchRateData()
    {
        $current = input::get('pages',1);
        $params = ['role'=>'seller','page_no'=>intval($current),'page_size'=>intval($this->limit),'fields'=>'*,append'];
        $filter = input::get();
        $pagedata['filter'] = $filter;

        if( input::get('content') )
        {
            $params['is_content'] = true;
        }
        if( input::get('picture') )
        {
            $params['is_pic'] = true;
        }

        if( input::get('is_reply') )
        {
            $params['is_reply'] = input::get('is_reply');
        }

        if( input::get('item_title') )
        {
            $params['item_title'] = input::get('item_title');
        }

        if( input::get('rate_time') )
        {
            $timeData = explode('-', input::get('rate_time'));
            $startTime = explode('/',trim($timeData[0]));
            $endTime = explode('/',trim($timeData[1]));
            $appealStartTime = mktime(0,0,0,$startTime[1],$startTime[2],$startTime[0]);
            $appealEndTime = mktime(24,60,60,$endTime[1],$endTime[2],$endTime[0]);
            $params['rate_start_time'] = $appealStartTime;
            $params['rate_end_time'] = $appealEndTime;
        }

        if( in_array(input::get('result'), ['good','bad', 'neutral']) )
        {
            $params['result'] = input::get('result');
        }
        $data = app::get('topshop')->rpcCall('rate.list.get', $params,'seller');
        $pagedata['rate']= $data['trade_rates'];
        foreach( $pagedata['rate'] as $k=>$row)
        {
            $userId[] = $row['user_id'];
            if( $row['rate_pic'] )
            {
                $pagedata['rate'][$k]['rate_pic'] = explode(',', $row['rate_pic']);
            }

            if( $row['append']['append_rate_pic'] )
            {
                $pagedata['rate'][$k]['append']['append_rate_pic'] = explode(',',$row['append']['append_rate_pic']);
            }
        }

        if( $userId )
        {
            $pagedata['userName'] = app::get('topshop')->rpcCall('user.get.account.name', ['user_id' => implode(',', $userId)], 'seller');
        }

        $pagedata['total'] = $data['total_results'];
        //处理翻页数据
        $filter = input::get();
        $filter['pages'] = time();
        if($data['total_results']>0) $total = ceil($data['total_results']/$this->limit);
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_rate@index',$filter),
            'current'=>$current,
            'use_app'=>'topshop',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        return $pagedata;
    }

    //评价详情
    public function detail()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('评价管理-评价详情');

        $pagedata['type'] = input::get('type',false);

        $params = ['role'=>'seller','rate_id'=>input::get('rate_id',false),'fields'=>'*,append'];
        $data = app::get('topshop')->rpcCall('rate.get', $params,'seller');
        if( empty($data) )
        {
            return redirect::action('topshop_ctl_rate@index');
        }

        if($data['rate_pic'])
        {
            $data['rate_pic'] = explode(',',$data['rate_pic']);
        }

        if( $data['append']['append_rate_pic'] )
        {
            $data['append']['append_rate_pic'] = explode(',',$data['append']['append_rate_pic']);
        }

        $pagedata['rate'] = $data;
        return $this->page('topshop/rate/detail.html', $pagedata);
    }

    //回复评价
    public function reply()
    {
        $replyType = input::get('type', 'add');

        $params['rate_id'] = input::get('rate_id');
        $params['reply_content'] = trim(input::get('reply_content'));

        $validator = validator::make(
            ['reply_content'=>$params['reply_content']],
            ['reply_content'=>'required|min:5|max:300'],
            ['reply_content'=>'回复内容必填|回复内容最少5个字|回复内容最多300个字']
        );
        if ( $validator->fails() )
        {
            $msg = $validator->messages()->first('reply_content');
            return $this->splash('error',$url,$msg,true);
        }

        try {
            if( $replyType == 'add')
            {
                $result = app::get('topshop')->rpcCall('rate.reply.add', $params,'seller');
            }
            else
            {
                $params['shop_id'] = $this->shopId;
                $result = app::get('topshop')->rpcCall('rate.append.reply', $params);
            }

            $status = $result ? 'success' : 'error';
            $msg = $result ? app::get('topshop')->_('回复成功') : app::get('topshop')->_('回复失败');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $status = 'error';
        }

        if( $status == 'success' )
        {
           $this->sellerlog('回复评价。回复评价id是'.$params['rate_id']);
        }
        $url = url::action('topshop_ctl_rate@detail',['rate_id'=>$params['rate_id']]);
        return $this->splash($status,$url,$msg,true);
    }
}


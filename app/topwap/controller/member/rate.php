<?php

/**
 * rate.php 会员中心评价
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topwap_ctl_member_rate extends topwap_ctl_member {

    /**
     * 评价分页显示，页显示数量
     */
    public $limit = 6;

    public function createRate()
    {
        $tid = input::get('tid');

        $tradeFiltr = array(
                'tid' => $tid,
                'fields' => 'tid,user_id,buyer_rate,shop_id,status,created_time,end_time,orders.oid,anony,orders.user_id,orders.price,orders.item_id,orders.sku_id,orders.title,orders.pic_path,orders.num',
        );

        $tradeInfo= app::get('topwap')->rpcCall('trade.get', $tradeFiltr);

        if( $tradeInfo['buyer_rate'] == '0' && $tradeInfo['status'] == 'TRADE_FINISHED')
        {
            $tradeInfo['is_buyer_rate'] = true;
        }
        else
        {
            redirect::action('topwap_ctl_member@index')->send();exit;
        }

        if( $tradeInfo['user_id'] != userAuth::id() )
        {
            redirect::action('topwap_ctl_member@index')->send();exit;
        }

        $pagedata['tradeInfo'] = $tradeInfo;
        $pagedata['title'] = app::get('topwap')->_('评价');
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');

        $this->action_view = "topwap/member/rate/add.html";
        return $this->page($this->action_view, $pagedata);
    }

    //创建评价
    public function doCreateRate()
    {
        try{
            $this->__checkPostData(input::get());
        }
        catch(LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        $params['tid'] = input::get('tid');
        $params['tally_score'] = input::get('tally_score');
        $params['attitude_score'] = input::get('attitude_score');
        $params['delivery_speed_score'] = input::get('delivery_speed_score');
        $logistics_service_score = input::get('logistics_service_score');
        $params['logistics_service_score'] = empty($logistics_service_score) ? 5 : input::get('logistics_service_score');
        $anony = input::get('anony');
        foreach( input::get('rate_data') as $key=>$row )
        {
            if($row['result'] != "good" && !trim($row['content']))
            {
                $msg = app::get('topwap')->_('请填写评价内容');
                return $this->splash('error',null,$msg,true);
            }

            if(mb_strlen($row['content'],'UTF-8') > 300)
            {
                $msg = app::get('topwap')->_('评价内容字数超过300上限');
                return $this->splash('error',null,$msg,true);
            }

            $rateData[$key] = $row;
            if( $row['rate_pic'] )
            {
                $rateData[$key]['rate_pic'] = implode(',', $row['rate_pic']);
            }
            $rateData[$key]['anony'] = ($anony == 'true') ? 1 : 0;
        }
        $params['rate_data'] = json_encode($rateData);
        $msg = '';
        try
        {
            $result = app::get('topwap')->rpcCall('rate.add', $params, 'buyer');
        }
        catch(\Exception $e)
        {
            $result = false;
            $msg = $e->getMessage();
        }

        if( !$result )
        {
            return $this->splash('error',null,$msg,true);
        }

        $url = url::action('topwap_ctl_member_trade@tradeList');

        // 根据前端要求将成功提示置空
        $msg = app::get('topwap')->_('评价提交成功');
        return $this->splash('success',$url,$msg,true);
    }

    //用户中心评价列表
    public function index()
    {
        $pagedata = $this->__getItemData();
        return $this->page("topwap/member/rate/index.html",$pagedata);
    }

    //用户中心评价列表的数据页面
    public function ratelist()
    {
        $pagedata = $this->__getItemData();
        return  view::make('topwap/member/rate/list.html', $pagedata);
    }

    private function __getItemData()
    {
        $current = input::get('pages',1);
        $params = ['role'=>'buyer','page_no'=>intval($current),'page_size'=>intval($this->limit),'fields'=>'*,append'];
        $data = app::get('topwap')->rpcCall('rate.list.get', $params,'buyer');
        $pagedata['rate']= $data['trade_rates'];
        foreach( $pagedata['rate'] as $k=>$row)
        {
            if( $row['rate_pic'] )
            {
                $pagedata['rate'][$k]['rate_pic'] = explode(',', $row['rate_pic']);
            }

            if( $row['append']['append_rate_pic'] )
            {
                $pagedata['rate'][$k]['append']['append_rate_pic'] = explode(',', $row['append']['append_rate_pic']);
            }
        }

        if($data['total_results']>0)
        {
            $total = ceil($data['total_results']/$this->limit);
        }
        else
        {
            $total = 0;
        }

        $pagedata['totalPages'] = $total;
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');

        return $pagedata;
    }

    private function __checkPostData($postdata)
    {

        $validator = validator::make(
            [
                'tid' => $postdata['tid'],
                'tally_score' => $postdata['tally_score'],
                'attitude_score' => $postdata['attitude_score'],
                'delivery_speed_score' => $postdata['delivery_speed_score'],
            ],
            [
                'tid' => 'numeric|required',
                'tally_score' => 'numeric|required|between:1,5',
                'attitude_score' => 'numeric|required|between:1,5',
                'delivery_speed_score' => 'numeric|required|between:1,5',
            ],
            [
                'tid' => '必须为数字！| 订单号不能为空！',
                'tally_score' => '必须为数字！|描述相符评分必填！| 请对所有评价项进行评分',
                'attitude_score' => '必须为数字！|服务态度评分必填！|请对所有评价项进行评分',
                'delivery_speed_score' => '必须为数字！|发货速度评分必填！|请对所有评价项进行评分',
            ]
        );
        if ($validator->fails())
        {
            error_log(print_r($messages,1),3,DATA_DIR."/bbbb.log");
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new LogicException( $error[0] );
            }
        }
        return true;
    }
}


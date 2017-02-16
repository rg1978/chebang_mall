<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_clearing_settlement extends topshop_controller
{
    public $limit = 10;

    /**
     * 结算汇总
     * @return
     */
    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('商家结算汇总');

        $filter['shop_id'] = $this->shopId;

        $postSend = input::get();
        $page = $postSend['page'] ? $postSend['page'] : 1;

        if($postSend['timearea'])
        {
            $pagedata['timearea'] = $postSend['timearea'];
            $timeArray = explode('-', $postSend['timearea']);
            $filter['settlement_time_than']  = strtotime($timeArray[0]);
            $filter['settlement_time_lthan'] = strtotime($timeArray[1]);
        }
        else
        {
            $filter['settlement_time_than']  = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));
            $filter['settlement_time_lthan'] = strtotime(date('Y-m-t  23:59:59', strtotime('-1 month')));
            $pagedata['timearea'] = date('Y/m/01', strtotime('-1 month')).'-'.date('Y/m/t', strtotime('-1 month'));
        }

        if($postSend['settlement_type'])
        {
            $filter['settlement_status'] = $postSend['settlement_type'];
            $pagedata['settlement_type'] = $postSend['settlement_type'];
        }
        $filter['page_no'] = $page;
        $filter['page_size'] = $this->limit;

        try{
            $settlement_list = app::get('topshop')->rpcCall('clearing.getList',$filter);
        }
        catch(\LogicException $e)
        {
            $settlement_list = array();
        }

        $list = $settlement_list['list'];
        $count = $settlement_list['count'];
        foreach ($list as $key => $value)
        {
            $list[$key]['timearea'] = date('Y/m/d',$value['account_start_time']).'-'.date('Y/m/d',$value['account_end_time']);
        }

        $pagedata['settlement_list'] = $list;
        $pagedata['count'] = $count;

        //处理翻页数据
        $pagedata['limits'] = $limit = $this->limit;
        $postSend['page'] = time();
        $link = url::action('topshop_ctl_clearing_settlement@index',$postSend);
        $pagedata['pagers'] = $this->__pagers($pagedata['count'],$page,$limit,$link);

        return $this->page('topshop/clearing/settlement.html', $pagedata);
    }

    /**
     * 结算明细
     * @return
     */
    public function detail()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('商家结算明细');

        $filter['shop_id'] = $this->shopId;

        $postSend = utils::_filter_input(input::get());
        $page = $postSend['page'] ? $postSend['page'] : 1;

        if($postSend['timearea'])
        {
            $pagedata['timearea'] = $postSend['timearea'];
            $timeArray = explode('-', $postSend['timearea']);
            $filter['settlement_time_than']  = strtotime($timeArray[0]);
            $filter['settlement_time_lthan'] = strtotime($timeArray[1]);
        }
        else
        {
            $filter['settlement_time_than']  = strtotime("-7 day",strtotime(date('Y-m-d')));
            $filter['settlement_time_lthan'] = strtotime(date('Y-m-d'));
            $pagedata['timearea'] = date('Y/m/d', time()-3600*24*7) . '-' . date('Y/m/d', time());
        }

        if($postSend['settlement_type'])
        {
            $filter['settlement_type'] = $postSend['settlement_type'];
            $pagedata['settlement_type'] = $postSend['settlement_type'];
        }
        $filter['page_no'] = $page;
        $filter['page_size'] = $this->limit;

        $result = app::get('topshop')->rpcCall('clearing.detail.getlist',$filter);
        $pagedata['settlement_detail_list'] = $this->__getTradePaymentType($result['list']);
        $pagedata['count'] = $result['count'];

        //处理翻页数据
        $limit = $this->limit;
        $postSend['page'] = time();
        $link = url::action('topshop_ctl_clearing_settlement@detail',$postSend);
        $pagedata['pagers'] = $this->__pagers($pagedata['count'],$page,$limit,$link);
        return $this->page('topshop/clearing/settlement_detail.html', $pagedata);
    }

    private function __getTradePaymentType($list)
    {
        if(!$list)
        {
            return array();
        }
        $tids = array_column($list, 'tid');
        $tids = implode(',', $tids);
        $params['tids'] = $tids;
        $params['fields'] = 'pay_name';
        $params['status'] = 'succ';
        $data = app::get('topshop')->rpcCall('trade.payment.list', $params);

        foreach($list as &$row)
        {
            $row['pay_type'] = '--';
            if($row['settlement_fee']>=0)
            {
                $row['pay_type'] = $data[$row['tid']]['pay_name'] ? $data[$row['tid']]['pay_name'] : '--';
            }
        }

        return $list;
    }
    private function __pagers($count,$page,$limit,$link)
    {
        if($count>0)
        {
            $total = ceil($count/$limit);
        }
        $pagers = array(
            'link'=>$link,
            'current'=>$page,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;
    }

}

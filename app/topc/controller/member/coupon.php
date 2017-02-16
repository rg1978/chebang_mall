<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_member_coupon extends topc_ctl_member {

    public function couponList()
    {
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 12;
        $params = array(
            'page_no' => intval($filter['pages']),
            'page_size' => intval($pageSize),
            'fields' => '*',
            'user_id' => userAuth::id(),
            'is_valid' => '1',
        );
        $status = input::get('status','1');
        if( in_array($status, ['0', '1', '2']) )
        {
            $params['is_valid'] = $status;
        }
        $timesort = strtolower(trim(input::get('timesort')));
        if( in_array($timesort, ['end_time desc', 'end_time asc']) )
        {
            $params['orderBy'] = $timesort;
            $pagedata['timesort_click'] = 1;
        }
        $pricesort = strtolower(trim(input::get('pricesort')));
        if( in_array($pricesort, ['price desc', 'price asc']) )
        {
            $params['orderBy'] = $pricesort;
            $pagedata['pricesort_click'] = 1;
        }
        $couponListData = app::get('topc')->rpcCall('user.coupon.list', $params, 'buyer');

        $count = $couponListData['count'];
        $couponList = $couponListData['coupons'];

        foreach ($couponList as &$v)
        {
            // 获取店铺子域名
            $v['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$v['shop_id']))['subdomain'];
        }
        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['timesort'] = $timesort;
        $filter['pricesort'] = $pricesort;
        $filter['status'] = $status;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_member_coupon@couponList',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['couponList']= $couponList;
        $pagedata['count'] = $count;
        $pagedata['action'] = 'topc_ctl_member_coupon@couponList';
        $pagedata['now'] = time();
        $pagedata['status'] = $status;
        $pagedata['pricesort'] = $pricesort=='price asc' ? 'price desc' : 'price asc';
        $pagedata['timesort']  = $timesort=='end_time asc' ? 'end_time desc' : 'end_time asc';

        $pagedata['num']['0'] = app::get('topc')->rpcCall('user.coupon.count', ['is_valid'=>'0', 'user_id'=>userAuth::id()], 'buyer')['count'];
        $pagedata['num']['1'] = app::get('topc')->rpcCall('user.coupon.count', ['is_valid'=>'1', 'user_id'=>userAuth::id()], 'buyer')['count'];
        $pagedata['num']['2'] = app::get('topc')->rpcCall('user.coupon.count', ['is_valid'=>'2', 'user_id'=>userAuth::id()], 'buyer')['count'];

        $this->action_view = "coupon/list.html";
        return $this->output($pagedata);
    }

}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 实现商家报表定时任务
 * @auther gongjiapeng
 * @version 0.1
 *
 */
class sysstat_operatorshop_task
{

    /**
     * 得到昨日新添加会员以及会员总数,店铺，店铺数，商家，商家数
     * @param null
     * @return null
     */
    public function getMemeberInfo(array $params)
    {
        $userAccountMd = app::get('sysuser')->model('account');
        $sellerAccountMd = app::get('sysshop')->model('account');
        $shopMd = app::get('sysshop')->model('shop');
        $filter = array(
          'createtime|bthan'=>$params['time_start'],
          'createtime|lthan'=>$params['time_end']
        );
        $userAllcount = $userAccountMd->count();
        $userIncreCount = $userAccountMd->count($filter);

        $sellerAccount = $sellerAccountMd->count();
        $sellerNum = $sellerAccountMd->count($filter);

        $shopfilter = array(
          'open_time|bthan'=>$params['time_start'],
          'open_time|lthan'=>$params['time_end'],
          'status' => 'active'
        );
        $shopnum = $shopMd->count($shopfilter);
        $shopaccount = $shopMd->count();

        $rows['newuser'] = $userIncreCount;
        $rows['accountuser'] = $userAllcount;
        $rows['sellernum'] = $sellerNum;
        $rows['selleraccount'] = $sellerAccount;
        $rows['shopnum'] = $shopnum;
        $rows['shopaccount'] = $shopaccount;
        $rows['createtime'] = $params['time_insert'];
        //echo '<pre>';print_r($rows);exit();
        return $rows;
    }

    /**
     * 得到昨日会员下单排行榜
     * @param null
     * @return null
     */
    public function getMemeberOrderInfo(array $params)
    {
        $qb = app::get('systrade')->database()->createQueryBuilder();
        $qb->select('count(*) as userordernum ,sum(payment) as userfee,user_id as user_id')
           ->from('systrade_trade')
           ->where('status="TRADE_FINISHED"')
           ->andWhere('end_time>='.$qb->createPositionalParameter($params['time_start']))
           ->andWhere('end_time<'.$qb->createPositionalParameter($params['time_end']))
           ->groupBy('user_id')
           ->addOrderBy('userfee', 'desc');

        $rows = $qb->execute()->fetchAll();
        $userAccountMd = app::get('sysuser')->model('account');
        $userMd = app::get('sysuser')->model('user');
        foreach ($rows as $key => $value)
        {
            $experience = $userMd->getRow('experience',array('user_id'=>$value['user_id']));
            $userName = $userAccountMd->getRow('login_account,mobile,email',array('user_id'=>$value['user_id']));
            if($userName['login_account'])
            {
                $rows[$key]['user_name'] = $userName['login_account'];
            }
            elseif($userName['mobile'])
            {
                $rows[$key]['user_name'] = $userName['mobile'];
            }
            else
            {
                $rows[$key]['user_name'] = $userName['email'];
            }
            $rows[$key]['experience'] = $experience['experience'];
            $rows[$key]['createtime'] = $params['time_insert'];
        }
        //echo '<pre>';print_r($userName);exit();
        return $rows;

    }

    /**
     * 得到规定时间内的新添加的订单数、额，以完成的订单数、额,以退款订单数，额
     * @param null
     * @return null
     */
    public function getTradeInfo(array $params)
    {
        $newTradeQb = app::get('systrade')->database()->createQueryBuilder();
        //新添加的订单数、额，
        $newTradeQb->select('count(*) as new_trade ,sum(payment) as new_fee ,trade_from as stats_trade_from')
           ->from('systrade_trade')
           ->where('created_time>='.$newTradeQb->createPositionalParameter($params['time_start']))
           ->andWhere('created_time<'.$newTradeQb->createPositionalParameter($params['time_end']))
           ->groupBy('trade_from');
        $newTradeInfo = $newTradeQb->execute()->fetchAll();
        foreach ($newTradeInfo as $key => $value)
        {
          $newTrade[$value['stats_trade_from']] = $value;
        }

        //以完成的订单数、额
        $completeTradeQb = app::get('systrade')->database()->createQueryBuilder();
        $completeTradeQb->select('count(*) as complete_trade ,sum(payment) as complete_fee ,trade_from as stats_trade_from')
           ->from('systrade_trade')
           ->where('status="TRADE_FINISHED"')
           ->andWhere('end_time>='.$completeTradeQb->createPositionalParameter($params['time_start']))
           ->andWhere('end_time<'.$completeTradeQb->createPositionalParameter($params['time_end']))
           ->groupBy('trade_from');
        $completeTradeInfo = $completeTradeQb->execute()->fetchAll();
        foreach ($completeTradeInfo as $key => $value)
        {
          $completeTrade[$value['stats_trade_from']] = $value;
        }
        //以退款的订单数、额
        $refundTradeQb = app::get('systrade')->database()->createQueryBuilder();
        $refundTradeQb->select('count(R.refund_id) as refunds_num ,sum(R.cur_money) as refunds_fee ,T.trade_from as stats_trade_from')
           ->from('ectools_refunds', 'R')
           ->leftJoin('R', 'systrade_trade', 'T', 'R.tid=T.tid')
           ->where('R.finish_time>='.$refundTradeQb->createPositionalParameter($params['time_start']))
           ->andWhere('R.finish_time<'.$refundTradeQb->createPositionalParameter($params['time_end']))
           ->andWhere('R.status="succ"')
           ->groupBy('T.trade_from');
        $refundTradeInfo = $refundTradeQb->execute()->fetchAll();

        foreach ($refundTradeInfo as $key => $value)
        {
          $refundTrade[$value['stats_trade_from']] = $value;
        }

        //整合数据
        $type = array('pc','wap');
        foreach ($type as $key )
        {
          $data[$key]['new_trade'] = $newTrade[$key]['new_trade']?$newTrade[$key]['new_trade']:0;
          $data[$key]['new_fee'] = $newTrade[$key]['new_fee']?$newTrade[$key]['new_fee']:0;
          $data[$key]['complete_trade'] = $completeTrade[$key]['complete_trade']?$completeTrade[$key]['complete_trade']:0;
          $data[$key]['complete_fee'] = $completeTrade[$key]['complete_fee']?$completeTrade[$key]['complete_fee']:0;
          $data[$key]['refunds_num'] = $refundTrade[$key]['refunds_num']?$refundTrade[$key]['refunds_num']:0;
          $data[$key]['refunds_fee'] = $refundTrade[$key]['refunds_fee']?$refundTrade[$key]['refunds_fee']:0;
          $data[$key]['stats_trade_from'] = $key;
          $data[$key]['createtime'] = $params['time_insert'];
        }
        $data['all']['new_trade'] = $newTrade['pc']['new_trade']+$newTrade['wap']['new_trade'];
        $data['all']['new_fee'] = $newTrade['pc']['new_fee']+$newTrade['wap']['new_fee'];
        $data['all']['complete_trade'] = $completeTrade['pc']['complete_trade']+$completeTrade['wap']['complete_trade'];
        $data['all']['complete_fee'] = $completeTrade['pc']['complete_fee']+$completeTrade['wap']['complete_fee'];
        $data['all']['refunds_num'] = $refundTrade['pc']['refunds_num']+$refundTrade['wap']['refunds_num'];
        $data['all']['refunds_fee'] = $refundTrade['pc']['refunds_fee']+$refundTrade['wap']['refunds_fee'];
        $data['all']['createtime'] = $params['time_insert'];
        $data['all']['stats_trade_from'] = 'all';
        //echo '<pre>';print_r($data);exit();
        return $data;
    }


    /**
     * 得到昨日店铺排行榜
     * @param null
     * @return null
     */
    public function getShopOrderInfo(array $params)
    {
        $qb = app::get('systrade')->database()->createQueryBuilder();
        $qb->select('count(*) as shopaccountnum ,sum(payment) as shopaccountfee,shop_id as shop_id')
           ->from('systrade_trade')
           ->where('status="TRADE_FINISHED"')
           ->andWhere('end_time>='.$qb->createPositionalParameter($params['time_start']))
           ->andWhere('end_time<'.$qb->createPositionalParameter($params['time_end']))
           ->groupBy('shop_id')
           ->addOrderBy('shopaccountfee', 'desc');

        $rows = $qb->execute()->fetchAll();

        $shopMd = app::get('sysshop')->model('shop');
        foreach ($rows as $key => $value)
        {
            $shopName = $shopMd->getRow('shop_name',array('shop_id'=>$value['shop_id']));
            if($shopName['shop_name'])
            {
                $rows[$key]['shopname'] = $shopName['shop_name'];
                $rows[$key]['createtime'] = $params['time_insert'];
            }
            else
            {
                unset($rows[$key]);
            }

        }
        //echo '<pre>';print_r($rows);exit();
        return $rows;

    }

     /**
     * 得到昨日商品排行榜
     * @param null
     * @return null
     */
    public function getItemOrderInfo(array $params)
    {
        $qb = app::get('systrade')->database()->createQueryBuilder();
        $qb->select('sum(R.num) as amountnum ,sum(R.payment) as amountprice,R.shop_id as shop_id,R.item_id as item_id,R.title as title,
                    R.pic_path as pic_path,R.cat_id as cat_id')
           ->from('systrade_order','R')
           //->leftJoin('R', 'sysitem_item', 'I', 'R.item_id=I.item_id')
           ->where('status="TRADE_FINISHED"')
           ->andWhere('end_time>='.$qb->createPositionalParameter($params['time_start']))
           ->andWhere('end_time<'.$qb->createPositionalParameter($params['time_end']))
           ->groupBy('item_id')
           ->addOrderBy('amountprice', 'desc');

        $rows = $qb->execute()->fetchAll();

        $shopMd = app::get('sysshop')->model('shop');
        $catMd = app::get('syscategory')->model('cat');
        foreach ($rows as $key => $value)
        {
            //获取类目信息
            $catPath = $catMd->getRow('cat_path',array('cat_id'=>$value['cat_id']));
            $parentId = explode(",",$catPath['cat_path'])[1];
            $catInfo = $catMd->getRow('cat_id,cat_name',array('cat_id'=>$parentId));
            //获取店铺信息
            $shopName = $shopMd->getRow('shop_name',array('shop_id'=>$value['shop_id']));
            $rows[$key]['shop_name'] = $shopName['shop_name'];
            $rows[$key]['cat_id'] = $catInfo['cat_id'];
            $rows[$key]['cat_name'] = $catInfo['cat_name'];
            $rows[$key]['itemurl'] = url::action("topc_ctl_item@index",array('item_id'=>$value['item_id']));
            $rows[$key]['createtime'] = $params['time_insert'];
        }

        return $rows;

    }

    /**
     * 得到昨日商品收藏排行榜
     * @param null
     * @return null
     */
    public function getCollectItemInfo(array $params)
    {
        $qb = app::get('sysuser')->database()->createQueryBuilder();
        $qb->select('count(F.item_id) as collectnum ,F.item_id as item_id,F.goods_name as title,F.cat_id as cat_id,
                    F.image_default_id as pic_path,F.shop_id as shop_id')
           ->from('sysuser_user_fav','F')
           //->leftJoin('F', 'sysitem_item', 'I', 'F.item_id=I.item_id')
           ->where('create_time>='.$qb->createPositionalParameter($params['time_start']))
           ->andWhere('create_time<'.$qb->createPositionalParameter($params['time_end']))
           ->groupBy('F.item_id')
           ->addOrderBy('collectnum', 'desc');

        $rows = $qb->execute()->fetchAll();
        $shopMd = app::get('sysshop')->model('shop');
        $catMd = app::get('syscategory')->model('cat');
        foreach ($rows as $key => $value)
        {
            //获取类目信息
            $catPath = $catMd->getRow('cat_path',array('cat_id'=>$value['cat_id']));
            $parentId = explode(",",$catPath['cat_path'])[1];
            $catInfo = $catMd->getRow('cat_id,cat_name',array('cat_id'=>$parentId));
            //获取店铺信息
            $shopName = $shopMd->getRow('shop_name',array('shop_id'=>$value['shop_id']));
            $rows[$key]['shop_name'] = $shopName['shop_name'];
            $rows[$key]['cat_id'] = $catInfo['cat_id'];
            $rows[$key]['cat_name'] = $catInfo['cat_name'];
            $rows[$key]['itemurl'] = url::action("topc_ctl_item@index",array('item_id'=>$value['item_id']));
            $rows[$key]['createtime'] = $params['time_insert'];
        }

        return $rows;

    }

    /**
     * 得到昨日店铺收藏排行榜
     * @param null
     * @return null
     */
    public function getCollectShopInfo(array $params)
    {
        $qb = app::get('sysuser')->database()->createQueryBuilder();
        $qb->select('count(F.shop_id) as collectnum ,F.shop_id as shop_id,F.shop_name as shopname')
           ->from('sysuser_shop_fav','F')
           ->where('create_time>='.$qb->createPositionalParameter($params['time_start']))
           ->andWhere('create_time<'.$qb->createPositionalParameter($params['time_end']))
           ->groupBy('F.shop_id')
           ->addOrderBy('collectnum', 'desc');

        $rows = $qb->execute()->fetchAll();
        foreach ($rows as $key => &$value)
        {
            $rows[$key]['createtime'] = $params['time_insert'];
            // 获取店铺子域名
            $subdomain = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$value['shop_id']))['subdomain'];

            $rows[$key]['shopurl'] = url::action("topc_ctl_shopcenter@index",array('shop_id'=>$value['shop_id'],'subdomain'=>$subdomain));
        }

        return $rows;

    }


}

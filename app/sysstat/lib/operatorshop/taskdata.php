<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 实现商家报表返回数据
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package sysstat.lib.analysis
 */
class sysstat_operatorshop_taskdata 
{
    public function exec($params)
    {
        //得到规定时间内店铺排行数据并保存到数据库
        $statCollectShopInfo = kernel::single('sysstat_operatorshop_task')->getCollectShopInfo($params);
        $sysstatMdlCollectShop  = app::get("sysstat")->model("desktop_collect_shop");
        
        foreach ($statCollectShopInfo as $key => $value)
        {
            $desktopStatCollectShopId = $sysstatMdlCollectShop->getRow('collect_shop_id',array('createtime'=>$value['createtime'],'shop_id'=>$value['shop_id']));
            if(!is_null($desktopStatCollectShopId))
            {
                $value['collect_shop_id'] = $desktopStatCollectShopId['collect_shop_id'];
            }

            $sysstatMdlCollectShop->save($value);
        }
        //echo '<pre>';print_r($statCollectShopInfo);exit();
        //得到规定时间内商品排行数据并保存到数据库
        $statCollectItemInfo = kernel::single('sysstat_operatorshop_task')->getCollectItemInfo($params);
        $sysstatMdlCollectItem  = app::get("sysstat")->model("desktop_collect_item");
        
        foreach ($statCollectItemInfo as $key => $value)
        {
            $desktopStatCollectItemId = $sysstatMdlCollectItem->getRow('collect_item_id',array('createtime'=>$value['createtime'],'item_id'=>$value['item_id']));
            if(!is_null($desktopStatCollectItemId))
            {
                $value['collect_item_id'] = $desktopStatCollectItemId['collect_item_id'];
            }
            $sysstatMdlCollectItem->save($value);
        }
        //echo '<pre>';print_r($statCollectItemInfo);exit();
        // 得到规定时间内的新添加的会员和会员总数,商家数，商家总数，店铺数，店铺总数 保存
        $sysstatMdlUser  = app::get("sysstat")->model("desktop_stat_user");
        $memberInfo = kernel::single('sysstat_operatorshop_task')->getMemeberInfo($params);
        $statuId = $sysstatMdlUser->getRow('statu_id',array('createtime'=>$memberInfo['createtime']));
        if(!is_null($statuId))
        {
            $memberInfo['statu_id'] = $statuId['statu_id'];
        }
        $sysstatMdlUser->save($memberInfo); //保存

        //echo '<pre>';print_r($memberInfo);exit();
        // 得到规定时间内的会员下单排行榜数量 保存
        $memberOrderInfo = kernel::single('sysstat_operatorshop_task')->getMemeberOrderInfo($params);
        $sysstatMdlUserOrder  = app::get("sysstat")->model("desktop_stat_userorder");
        foreach ($memberOrderInfo as $key => $value)
        {
            $desktopStatOId = $sysstatMdlUserOrder->getRow('statu_oid',array('createtime'=>$value['createtime'],'user_id'=>$value['user_id']));
            if(!is_null($desktopStatOId))
            {
                $value['statu_oid'] = $desktopStatOId['statu_oid'];
            }
            $sysstatMdlUserOrder->save($value);
        }
        //echo '<pre>';print_r($memberOrderInfo);exit();

        // 得到规定时间内的新添加的订单数、额，以完成的订单数、额,以退款订单数，额  保存
        $tradeInfo = kernel::single('sysstat_operatorshop_task')->getTradeInfo($params);
        $sysstatMdlTradeStat  = app::get("sysstat")->model("desktop_trade_statics");
        foreach ($tradeInfo as $key => $value)
        {
            $desktopStatId = $sysstatMdlTradeStat->getRow('desktop_stat_id',array('createtime'=>$value['createtime'],'stats_trade_from'=>$key));
            if(!is_null($desktopStatId))
            {
                $value['desktop_stat_id'] = $desktopStatId['desktop_stat_id'];
            }
            $sysstatMdlTradeStat->save($value);
        }
        //echo '<pre>';print_r($params);exit();

        //得到规定时间内店铺排行数据并保存到数据库
        $statShopInfo = kernel::single('sysstat_operatorshop_task')->getShopOrderInfo($params);
        $sysstatMdlStatShop  = app::get("sysstat")->model("desktop_stat_shop");
        foreach ($statShopInfo as $key => $value)
        {
            $desktopStatshopId = $sysstatMdlStatShop->getRow('desktop_statshop_id',array('createtime'=>$value['createtime'],'shop_id'=>$value['shop_id']));
            if(!is_null($desktopStatshopId))
            {
                $value['desktop_statshop_id'] = $desktopStatshopId['desktop_statshop_id'];
            }
            $sysstatMdlStatShop->save($value);
        }
        //echo '<pre>';print_r($statShopInfo);exit();

         //得到规定时间内商品排行数据并保存到数据库
        $statItemInfo = kernel::single('sysstat_operatorshop_task')->getItemOrderInfo($params);
        $sysstatMdlStatItem  = app::get("sysstat")->model("desktop_item_statics");
        //echo '<pre>';print_r($statItemInfo);exit();
        foreach ($statItemInfo as $key => $value)
        {
            $desktopStatItemId = $sysstatMdlStatItem->getRow('desktop_item_stat_id',array('createtime'=>$value['createtime'],'item_id'=>$value['item_id']));
            if(!is_null($desktopStatItemId))
            {
                $value['desktop_item_stat_id'] = $desktopStatItemId['desktop_item_stat_id'];
            }
            $sysstatMdlStatItem->save($value);
        }

    }

}

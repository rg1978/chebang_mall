<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysrate_tasks_finishRate extends base_task_abstract implements base_interface_task {


    public function exec($params=null)
    {
        //15天内没有评价，自动好评
        $filter['buyer_rate'] = 0;
        $filter['disabled'] = 0;
        $filter['status'] = 'TRADE_FINISHED';
        $filter['end_time|lthan'] = strtotime('-15 days');

        $tradeData = app::get('systrade')->model('trade')->getList('tid,user_id', $filter);
        if( empty($tradeData) ) return true;

        $tids = array_column($tradeData,'tid');

        $orderData = app::get('systrade')->model('order')->getList('oid,tid',['tid'=>$tids]);
        foreach( $orderData as $row )
        {
            $oids[$row['tid']][] = $row['oid'];
        }

        $data = array();
        foreach($tradeData as $row)
        {
            $data['tid'] = $row['tid'];
            $data['user_id'] = $row['user_id'];
            $data['rate_data'] = [];
            foreach( $oids[$row['tid']] as $oid )
            {
                $data['rate_data'][] = [
                    'oid' => $oid,
                    'result' => 'good',
                    'content' => app::get('sysrate')->_('系统默认好评'),
                    'anony' => 1,
                ];
            }

            try
            {
                if( !kernel::single('sysrate_traderate')->add($data,false) )
                {
                    logger::info(app::get('sysrate')->_('评价添加失败'));
                }
            }
            catch( Exception $e )
            {
                logger::info($e->getMessage());
            }
        }

        return true;
    }
}


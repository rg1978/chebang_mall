<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysaftersales_task
{

    public function post_update($dbver)
    {
        if( 0.1 >= $dbver['dbver'] && $dbver['dbver'] < 0.2 )
        {
            app::get('sysaftersales')->model('refunds')->update(array('status'=>'3'),array('status'=>'0'));
        }

        if( 0.2 >= $dbver['dbver'] && $dbver['dbver']<0.3 )
        {
            $objMdlRefunds = app::get('sysaftersales')->model('refunds');
            $data = $objMdlRefunds->getList('refunds_id');
            foreach( $data as $row )
            {
                if( !$row['refund_bn'] )
                {
                    $sign = '2'.date("Ymd");
                    $microtime = microtime(true);
                    mt_srand($microtime);
                    $randval = substr(mt_rand(), 0, -3) . rand(100, 999);
                    $refundBn = $sign.$randval;
                    $objMdlRefunds->update(array('refund_bn'=>$refundBn),array('refunds_id'=>$row['refunds_id']));
                }
            }
        }

        if(0.3 <= $dbver['dbver'] && $dbver['dbver'] < 0.4)
        {
            $objMdlRefunds = app::get('sysaftersales')->model('refunds');
            $data = $objMdlRefunds->getList('total_price,refunds_id');
            foreach( $data as $row )
            {
                $objMdlRefunds->update(array('refund_fee'=>$row['total_price']),array('refunds_id'=>$row['refunds_id']));
            }
        }
    }

}


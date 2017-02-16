<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class update extends PHPUnit_Framework_TestCase {

    public function test()
    {
        $objMdlRefunds = app::get('sysaftersales')->model('refunds');
        $data = $objMdlRefunds->getList('*');
        foreach($data as $row)
        {
            $tid = unserialize($row['tid']);
            $oid = unserialize($row['oid']);
            if( $tid )
            {
                $objMdlRefunds->update(['tid'=>$tid],['refunds_id'=>$row['refunds_id']]);
            }

            if( $oid )
            {
                $objMdlRefunds->update(['oid'=>$oid],['refunds_id'=>$row['refunds_id']]);
            }
        }
        echo '更新退款申请单表数据OK';
    }
}

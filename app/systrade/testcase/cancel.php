<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class cancel extends PHPUnit_Framework_TestCase {

    public function testCancel()
    {
        $minuteTime = 60;
        $tid = '1601151754390023';
        $cancelReason = "订单未在下单".$minuteTime."分钟内完成支付,被系统自动关闭。";
        $result = kernel::single('systrade_data_trade_cancel')
            ->setCancelFromType('system')
            ->create($tid, $cancelReason);
        var_dump($result);
    }
}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class notifyPrism extends PHPUnit_Framework_TestCase {

    public function testNotifyPrism()
    {
        $objPrismNotify = kernel::single('system_prism_notify');
        $tid = '1508211523330001';
        $shopId = 1;
        $notifyData['prismNotifyName'] = 'test';
        $notifyData['tid'] = $tid;
        $notifyData['shop_id'] = $shopId;
        try{
            $result = $objPrismNotify->write($shopId, $notifyData);
        }catch( \Exception $e )
        {
            echo '推送失败';
            exit;
        }

        echo '推送成功';
    }
}


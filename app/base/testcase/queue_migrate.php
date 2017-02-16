<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class queue_migrate extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testRedisMigrateScript(){
        $objectReids = redis::scene('queue');
        $objectReids->loadScripts('queueMigrate');
        $from='quick:reserved';
        $to='queue:quick';
        $v = $objectReids->queueMigrate($from, $to, time());
        var_dump($v);
    }
}

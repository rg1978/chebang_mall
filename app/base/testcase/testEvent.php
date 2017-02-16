<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class testEvent extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testFireEvent()
    {
        $data = ['1',2,3];
        event::listen('test', function ($data) {
            echo '监听任务为闭包参数'."\n";
            print_r($data);
        });

        event::fire('test', array(['key1'=>'value1','key2'=>'value2']) );
    }
}


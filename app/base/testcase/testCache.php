<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class testCache extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function testPutAndGet()
    {
        cache::store('misc')->put('dd', 20, 10);
        $this->assertEquals(cache::store('misc')->get('dd'), 20);
    }

    public function testGetMisc()
    {
        $this->assertEquals(cache::store('misc')->get('future', function () {return 'misc';}), 'misc');
    }

    public function testFlush()
    {
        cache::store('misc')->put('test:dd', 21, 1);
        cache::store('misc')->flush();
        $this->assertEquals(cache::store('misc')->get('test:dd'), null);
    }

    public function testIncrement()
    {
        cache::store('misc')->put('xxincre', 0, 0);
        $this->assertEquals(cache::store('misc')->increment('xxincre', 1, 0, 10), 1);
        $this->assertEquals(cache::store('misc')->increment('xxincre', 1), 2);
        
        //        echo 99;exit;
    }

    public function testIncrementAndDecrement()
    {
        //        cache::store('misc')->put('xxincrejian', 0, 0);
        $this->assertEquals(cache::store('misc')->decrement('xxincrejian', 1, 30, 10), 29);
        $this->assertEquals(cache::store('misc')->decrement('xxincrejian', 1), 28);
        $this->assertEquals(cache::store('misc')->decrement('xxincrejian', 5), 23);
        $this->assertEquals(cache::store('misc')->decrement  ('xxincrejian', 331), 0);
        
        
        //        echo 99;exit;
    }

    public function testForeverAndForget()
    {
        cache::store('misc')->rememberForever('xiaolu1', function() {return 'xl1';});

        $this->assertEquals(cache::store('misc')->get('xiaolu1'), 'xl1');

        cache::store('misc')->forget('xiaolu1');

        $this->assertEquals(cache::store('misc')->get('xiaolu1'), null);

        
    }

    public function testDisable()
    {
        cache::store('misc')->put('t:ddddd', 20, 10);
        cache::disable();
        $this->assertEquals(cache::store('misc')->get('t:ddddd'), null);
        cache::enable();
        $this->assertEquals(cache::store('misc')->get('t:ddddd'), 20);
    }

    public function testHasAndForget()
    {
        cache::store('misc')->put('t:ddddd', 20, 10);
        $this->assertEquals(cache::store('misc')->get('t:ddddd'), 20);
        $this->assertEquals(cache::store('misc')->has('t:ddddd'), true);
        cache::store('misc')->forget('t:ddddd');
        $this->assertEquals(cache::store('misc')->has('t:ddddd'), false);
        $this->assertEquals(cache::store('misc')->get('t:ddddd'), null);
    }

    public function testForever()
    {
        cache::store('misc')->forever('aaa', 30);
        $this->assertEquals(cache::store('misc')->get('aaa') , 30);
    }

    
}


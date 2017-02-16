<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class queue_consumer extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        ob_implicit_flush(1);
        $this->obj_queue = system_queue::instance();
    }


    public function testConsume(){
        $t = microtime();
        $n = 1;
        for($i=0; $i<$n; $i++){
            if ($queueMessgage = $this->obj_queue->get('quick')) {
                $this->obj_queue->consumer($queueMessgage);
            }
        }
        var_dump(microtime() - $t);
    }
}




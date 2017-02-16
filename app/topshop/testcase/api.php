<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class api extends PHPUnit_Framework_TestCase{


    public function testApi(){
        var_dump(app::get('topshop')->rpcCall('category.cat.get.list'));
    }
}


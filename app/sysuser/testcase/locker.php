<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class locker extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testRequest(){
      //kernel::single('sysuser_data_passwordLocker')->write(1,'deposit_password',2);
      //$times = kernel::single('sysuser_data_passwordLocker')->read(1,'deposit_password');
      //$times = kernel::single('sysuser_data_passwordLocker')->incr(2,'deposit_password');
      //$times = kernel::single('sysuser_data_passwordLocker')->clean(1,'deposit_password');
      //$times = kernel::single('sysuser_data_passwordLocker')->getExpire('deposit_password');
      //$times = kernel::single('sysuser_data_passwordLocker')->getLimit('deposit_password');
        var_dump($times);
    }
}

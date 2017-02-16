<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class dedectDeposit extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testAddDepost(){
        $userId = 1;
        $operator = 'admin';
        $fee = 1;

        $deposit = kernel::single('sysuser_data_deposit_deposit')->dedect($userId, $operator, $fee);
        $deposit = kernel::single('sysuser_data_deposit_deposit')->get($userId);
        print_r($deposit);



    }
}

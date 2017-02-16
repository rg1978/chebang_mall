<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class addDeposit extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testAddDepost(){
        $userId = 2;
        $operator = 'admin';
        $fee = 1;

        $deposit = kernel::single('sysuser_data_deposit_deposit')->add($userId, $operator, $fee);
        $deposit = kernel::single('sysuser_data_deposit_deposit')->get($userId);
        print_r($deposit);



    }
}

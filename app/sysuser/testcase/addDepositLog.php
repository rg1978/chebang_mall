<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class addDepositLog extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testAddDepost(){
        $userId = 1;
        $operator = 'admin';
        $fee = 11;
        $msg = '那个';

        kernel::single('sysuser_data_deposit_log')->addLog($userId, $operator, $fee, $msg);
        $list  = kernel::single('sysuser_data_deposit_log')->getAll($userId);
        print_r($list);

    }
}

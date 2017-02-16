<?php

class depositPassword extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {

    }

    public function testHasPassword()
    {

        $userId = 1;
        $password = 'aaaaaaaa';
        $newPassword = 'bbbbbbb';

        //hasPassword
      //$deposit = kernel::single('sysuser_data_deposit_password')->hasPassword($userId);
        //setPassword
      //$deposit = kernel::single('sysuser_data_deposit_password')->setPassword($userId, $password);
        //changePassword
      //$deposit = kernel::single('sysuser_data_deposit_password')->changePassword($userId, $password, $newPassword);
        //checkPassword
      //$deposit = kernel::single('sysuser_data_deposit_password')->checkPassword($userId, $newPassword);

        echo "\n==========\n";
        var_dump($deposit);
        echo "\n==========\n";
        return ;
    }

}




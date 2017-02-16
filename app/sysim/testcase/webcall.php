<?php
class webcall extends PHPUnit_Framework_TestCase
{
    public function setUp(){
    }

    public function testAddAccount()
    {
        $requester = kernel::single('sysim_webcall_request');

        $email = '1112@ddd.com';
        $pwd = 'xinxin123';
        $name = 'fewafewa';
        $res = $requester->addAccount($email, $pwd, $name);

        var_dump($res);

    }

}

?>

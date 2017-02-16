<?php
class base_rpc_check{

    function handshake(){
        if($value = redis::scene('system')->get('net.handshake')){
            echo $value;
        }else{
            $code = md5(microtime());
            redis::scene('system')->set('net.handshake',$code);
            echo $code;
        }
    }

    function login_hankshake()
    {
        if($value = redis::scene('system')->get('net.login_handshake')) {
            echo $value;
        }else{
            $code = md5(microtime());
            redis::scene('system')->set('net.login_handshake', $code);
            echo $code;
        }
    }

    function check_sys(){
        kernel::single('dev_command_syscheck')->command_allcheck();
    }
}

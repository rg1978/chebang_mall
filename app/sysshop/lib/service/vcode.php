<?php

class sysshop_service_vcode {

    public function status()
    {
        pamAccount::setAuthType('sysshop');

        $errorCount = pamAccount::getLoginErrorCount();
        //没开启验证码必填的情况下，错误三次及其以上则需要验证码
        return ($errorCount >= 3) ?  true : false;

    }
}

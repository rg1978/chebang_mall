<?php
class topc_ctl_wechat extends topc_controller{
// 微信扫码支付回调地址
    function wxqrpay(){
        $postData = array();
        $httpclient = kernel::single('base_httpclient');
        $callback_url = kernel::openapi_url('openapi.ectools_payment/parse/ectools_payment_plugin_wxqrpay', 'callback');
        $postStr = file_get_contents("php://input");//$GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData['weixin_postdata']  = $postArray['xml'];
        $nodify_data = array_merge(input::get(),$postData);
        $response = $httpclient->post($callback_url, $nodify_data);
        //error_log(var_export($response,1),3,'/tmp/1.txt');
    }
}

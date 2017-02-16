<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class postApi extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //测试API直连，运营平台权限，不需要oauth
        $this->url = 'http://192.168.65.145/bbc/public/index.php/api';
    }

    public function testRequest() {
        $sysParams['method'] = 'trade.get.list';
        $sysParams['timestamp'] = time();
        $sysParams['format'] = 'json';
        $sysParams['v'] = 'v1';
        $sysParams['sign_type'] = 'MD5';
        $apiParams['fields'] = '*';
        $params = array_merge($sysParams, $apiParams);

        $params['sign'] = base_rpc_validate::sign($params,base_certificate::token());

        client::post($this->url, ['body' => $params]);

        logger::info(print_r($response));
    }
}

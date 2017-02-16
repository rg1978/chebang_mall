<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class rpc extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testRequest()
    {

        $a = file_get_contents('/Users/Bryant/codes/bbc/config/deploy.xml');
        var_dump(kernel::single('site_utility_xml')->xml2array($a));
        exit;

        
        client::get('http://localhost:8080/?dkskf&dsf=3');
        //插入一条服务器消息
//        $server = array(
//                'node_id'=>'5',
//                'node_url'=>kernel::base_url(),
//                'node_name'=>'localhost',
//                'node_api'=>'index.php/api',
//                'sitekey'=>md5(123456),
//            );
//        app::get('base')->model('network')->replace($server,array('node_id'=>5));
    }
}

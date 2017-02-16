<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

class routeX extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testRoute()
    {
        route::group(['domain' => '{fff:(?!www)[\w\d-]+}.bbc.me'], function() {
    
            route::get('/', ['as' => 'aaa', function ($a) {
                    echo '<pre>';
                    var_dump(route::currentParameters());exit;
                    echo url::action('topshop_ctl_passport@signin');exit;
                    echo url::route('ccccc', ['fff' => 'fffdd', 'idx' => '123']);
                    echo '<br>';
                    echo 'xx';
                    var_dump($a);
                }]);
            route::get('/ddd-{idx:[0-9]*}', ['as' => 'ccccc' ,function($aa, $idx){
            
                    echo $aa.'-'.$idx;exit;
                }]);
        });
    }
}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use Predis\Response\ServerException;

class testRedis extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

        //$this->model = app::get('base')->model('members');
    }

    public function testHget() {
        #echo 'kkkk'.PHP_EOL;
        #echo redis::scene('system')->hget('desktop_application_adminpanel.systrade');
        #echo 'stopkkkk'.PHP_EOL;
        #echo '----------';
        #//        require "/Users/bryant/codes/bbc/app/base/lib/exception/handler1.php";
        #$a = new base_exception_handle;
        #$sceneRedis = redis::scene('common');

        #forward_static_call_array([$sceneRedis, 'hmset'], ['bbb']);
        #var_dump(redis::scene('common')->hgetall('bbb'));
    }

    public function testRequest()
    {
        #redis::scene('queue')->zadd('queue:bb:cc',1,121231);
        #redis::scene('queue')->zadd('queue:bb:cc',2,3333);
        #redis::scene('queue')->zadd('queue:bb:cc',3,444);
        $data = redis::scene('queue')->zcount('normal:reserved', time()-3360, "+inf" );
        print_r($data);

        //redis::scene('queue')->loadScripts('queueGet');

        //redis::scene('queue')->set('ax', 333);
        //var_dump(redis::scene('queue')->get('ax'));
        //exit;
        #redis::scene('queue')->rpush('dd' ,  'bar0' ) ; //3
        #redis::scene('queue')->rpush('dd' ,  'bar2' ) ; //3
        #redis::scene('queue')->rpush('dd' ,  'bar3' ) ; //3

        #$data =  redis::scene('queue')->lrange('dd',0,-1); //3
        #var_dump($data);

        //redis::scene('queue')->ltrim('dd',-1,0) ;

        //$data =  redis::scene('queue')->lrange('dd',0,-1); //3
        //var_dump($data);


        #redis::scene('queue')->lpop('dd') ; //3


        #$job =  redis::scene('queue')->lpop('dd') ; //3
        #echo $job;
        #if( $job )
        #{
        #    redis::scene('queue')->zadd('dd:reserved', time() + 60, $job);
        #    echo '鎵ц����闃熷垪'.$job;

        #    #redis::scene('queue')->zrem('dd:reserved', $job);
        #    #echo '绉婚櫎闃熷垪'.$job;
        #}

        #$data =  redis::scene('queue')->zrange('dd:reserved',0,-1); //3
        #var_dump($data);

        //echo redis::scene('queue')->lpop('dd') ; //3
        //var_dump((string)redis::scene('queue')->ping()==='PONG');

        #echo redis::scene('queue')->rpush('dd' ,  'bar100' ) ; //3
    }
}

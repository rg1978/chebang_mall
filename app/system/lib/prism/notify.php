<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class system_prism_notify {

    static private $__conn = array();

    protected $notifyTopic = 'messages';

    protected $notifyRoutingKey = 'messages';

    public function conn($shopId)
    {
        if( self::$__conn[$shopId] )
        {
            return self::$__conn[$shopId];
        }
        else
        {
            $this->__get_connection($shopId);
        }

        return $this;
    }

    public function write($shopId, $data)
    {
        $this->conn($shopId);

        $notifyMessage = json_encode($data);

        for( $i=0; $i<5; $i++ )
        {
            try
            {
                $res = self::$__conn[$shopId]->post('/platform/notify/write', array('topic'=>$this->notifyTopic, 'data'=>$notifyMessage));
            }
            catch( Exception $e )
            {

                if( $i == 4)
                {
                    logger::info('/platform/notify/write error message: '.$e->getMessage());
                    logger::info('/platform/notify/write notifyMessage: '.var_export($notifyMessage,1));
                    throw new \Exception("prism消息推送错误 message=>".$e->getMessage());
                }
                else
                {
                    continue;
                }
            }

            break;
        }

        return $res['result'];
    }

    /**
     * 配置消息路由
     */
    public function publish()
    {
        $conn = system_prism_init_util::getAdminConn();

        $appId = 'openstandard';
        $primsAppId = system_prism_init_util::getAppId($appId);
        $params['app_id'] = $primsAppId;

        $params['routing_key'] = json_encode([$this->notifyRoutingKey]);
        logger::info('params : queue publish '.var_export($params));

        return kernel::single('system_prism_init_base')->call( $conn, '/api/platform/manageapp/queue/publish', $params, 'post' );
    }

    /**
     * 配置可消费消息路由
     */
    public function consume()
    {
        $conn = system_prism_init_util::getAdminConn();

        $appId = 'openstandard';
        $primsAppId = system_prism_init_util::getAppId($appId);
        $params['app_id'] = $primsAppId;

        $params['routing_key'] = json_encode([$this->notifyRoutingKey]);

        logger::info('params : queue consume '.var_export($params));
        return kernel::single('system_prism_init_base')->call( $conn, '/api/platform/manageapp/queue/consume', $params, 'post' );
    }

    public function setRouting($shopId)
    {
        $this->conn($shopId);

        $res = self::$__conn[$shopId]->post('/platform/notify/routing/set', array('topic'=>$this->notifyTopic, 'routing_key'=>$this->notifyRoutingKey));
        return $res['result'];
    }

    private function __get_connection($shopId)
    {
        $keySecret = kernel::single('sysopen_shop_info')->getShopOpenInfo($shopId);
        $key = $keySecret['key'];
        $secret = $keySecret['secret'];

        $host = config::get('prism.prismHostUrl');
        $host = rtrim($host, '/') . '/api';

        //这个是长连接的配置
        $socketFile = config::get('prism.prismSocketFile');

        //创建队列传输链接
        self::$__conn[$shopId] = new base_prism_client($host, $key, $secret, $socketFile);

        return self::$__conn[$shopId];
    }
}


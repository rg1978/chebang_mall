<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author guocheng
 */

class system_command_prism extends base_shell_prototype{

    public $command_init = "初始化api信息到prism";
    public function command_init()
    {
        $flag = kernel::single('system_prism_store')->get('prismInitComplete');
        if($flag)
        {
            logger::info('prism is inited. if you want to init, please run forceinit');
        }
        else
        {
            kernel::single('system_prism_init')->init();
        }
        logger::info('finished');
    }

    public $command_forceinit = "强制初始化api信息到prism（慎用）";
    public function command_forceinit()
    {
        kernel::single('system_prism_init')->init();
        logger::info('finished');
    }

    public $command_update = "更新prism上的API数据";
    public function command_update()
    {
        if( !(config::get('prism.prismMode') == true) )
        {
            logger::info('prismMode is closed. if you want to update, please opened it in "config/prism.php"');
            return;
        }
        kernel::single('system_prism_init')->update();
        logger::info('finished');
    }

    public $command_forceupdate = "强制更新api信息到prism（慎用）";
    public function command_forceupdate()
    {
        kernel::single('system_prism_init')->update();
        logger::info('finished');
    }

    public $command_oauthUpdate = "更新prism端口的Oauth配置数据";
    public function command_oauthUpdate()
    {
        $flag = kernel::single('system_prism_store')->get('prismInitComplete');
        if(!flag)
        {
            logger::info('please run "system:prism init" before.');
            return null;
        }
        kernel::single('system_prism_init')->oauthUpdate();
        logger::info('finished');
    }

    public $command_initNotify = '初始化BBC消息推送到prism设置';
    public function command_initNotify()
    {
        $result = kernel::single('system_prism_notify')->publish();
        if( ! $result['queue_publish'] )
        {
            logger::info('ERROR : queue publish fail');
        }

        logger::info('FINISHED : queue publish success');

        $res = kernel::single('system_prism_notify')->consume();
        if( ! $res['queue_consume'] )
        {
            logger::info('ERROR : queue consume fail');
        }

        logger::info('FINISHED : queue consume success');

        return true;
    }

    public $command_initShopRouting = '初始化店铺消息路由';
    public function command_initShopRouting()
    {
        $data = app::get('sysopen')->model('keys')->getList('shop_id,contact_type');
        $objPrismNotify = kernel::single('system_prism_notify');

        foreach( $data as $row )
        {
            if( $row['contact_type'] == 'notallowopen' || $row['contact_type'] == 'applyforopen' ) continue;

            $result = $objPrismNotify->setRouting($row['shop_id']);

            if( $result == 'error' )
            {
                logger::info("fail shopId =>".$row['shop_id']);
                break;
            }
            else
            {
                logger::info("seccuess shopId=>".$row['shop_id']);
            }
        }
    }
}


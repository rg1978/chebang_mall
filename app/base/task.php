<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

use Predis\Client as RedisClient;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class base_task
{
    public function install_options(){
        return array(
            'database.connections.default.host'=>array('type'=>'text','vtype'=>'required','required'=>true,'title'=>app::get('base')->_('数据库主机'),'default'=>'127.0.0.1'),
            'database.connections.default.user'=>array('type'=>'text','vtype'=>'required','required'=>true,'title'=>app::get('base')->_('数据库用户名'),'default'=>'root'),
            'database.connections.default.password'=>array('type'=>'password','title'=>app::get('base')->_('数据库密码'),'default'=>''),
            'database.connections.default.dbname'=>array('type'=>'select','vtype'=>'required','required'=>true,'title'=>app::get('base')->_('数据库名'),'options_callback'=>array('app'=>'base', 'method'=>'dbnames'),'onfocus'=>'setuptools.getdata(\'base\', \'dbnames\', this);'),
            'app.timezone'=>array('type'=>'select','options'=>base_location::timezone_list()
                                      ,'title'=>app::get('base')->_('默认时区'),'default'=>'8','vtype'=>'required','required'=>true),
            'app.url'=>array('type'=>'text', 'title'=>app::get('base')->_('系统URL地址'),'default'=>$this->getDefaultAppUrl(),'vtype'=>'required','required'=>true),
            'redis.connection' => ['type' => 'text', 'title' => app::get('base')->_('redis配置'), 'default' => 'tcp://127.0.0.1:6379', 'vtype' => 'required', 'required' => true], 
            //'ceti_identifier'=>array('type'=>'text','vtype'=>'required','required'=>true,'title'=>app::get('base')->_('电子邮箱或企业帐号'),'default'=>''),
            //'ceti_password'=>array('type'=>'password','vtype'=>'required','required'=>true,'title'=>app::get('base')->_('密码'),'default'=>''),
        );
    }

    private function getDefaultAppUrl() {
        if (kernel::runningInConsole()) {
            return 'http://localhost';
        } else {
            return request::root();
        }
        
    }

    public function dbnames($options)
    {
        $options = $options['base'];

        $config = new Doctrine\DBAL\Configuration();

        try {
            $conn = Doctrine\DBAL\DriverManager::getConnection(['driver' => 'mysqli',
                                                                'host' => $options['database.connections.default.host'],
                                                                'user' => $options['database.connections.default.user'],
                                                                'password' => $options['database.connections.default.password'],
                                                                'charset' => 'utf8'],
                                                               $config);
            if ($conn->connect() === false) return [];
        } catch (Exception $e) {
            $conn->close();
            return [];
        }
        $schema = $conn->getSchemaManager();

        $databaseList = [];
        array_walk($schema->listDatabases(), function($v, $k) use (&$databaseList) {
            $databaseList[$v] = $v;
        });
        $conn->close();

        return $databaseList;
    }
    
    public function checkenv($options){
        if ($options['config-install']) {
            echo app::get('base')->_('通过配置进行安装.');
            return true;
        }
        
        if(!$options['database.connections.default.host']){
            echo app::get('base')->_("Error: 需要填写数据库主机")."\n";
            return false;
        }
        if(!$options['database.connections.default.user']){
            echo app::get('base')->_("Error: 需要填写数据库用户名")."\n";
            return false;
        }
        if(!$options['database.connections.default.dbname']){
            echo app::get('base')->_("Error: 请选择数据库")."\n";
            return false;
        }


        $link = @mysql_connect($options['database.connections.default.host'],
                               $options['database.connections.default.user'],
                               $options['database.connections.default.password']);
        if(!$link){
            echo app::get('base')->_("Error: 数据库连接错误")."\n";
            return false;
        }

        $mysql_ver = mysql_get_server_info($link);
        if(!version_compare($mysql_ver,'4.1','>=')){
            echo app::get('base')->_("Error: 数据库需高于4.1的版本")."\n";
            return false;
        }
        
        if(!mysql_select_db($options['database.connections.default.dbname'], $link)){
            echo app::get('base')->_("Error: 数据库")."\"" . $options['database.connections.default.dbname'] . "\"".app::get('base')->_("不存在")."\n";
            return false;
        }

        try {
            $redisClient = new RedisClient($options['redis.connection'], $options);
            $redisClient->ping();
            
        } catch (Exception $e) {
            echo "Error: Redis连接错误";
            return false;
        }

        return true;
    }

    public function pre_install($options){
        // 如果不是通过配置直接进行安装, 那么久要初始化数据
        if (!$options['config-install']) {
            foreach ($options as $key => $value) {
                config::set($key, $value);
            }

            if(!kernel::single('base_setup_config')->write($options)){
                echo app::get('base')->_("Error: Config文件写入错误")."\n";
                return false;
            }            
        }

        $timezone = config::get('app.timezone', 8);
        date_default_timezone_set('Etc/GMT'.($timezone>=0?($timezone*-1):'+'.($timezone*-1)));
        
        redis::flushAllResources();
        return true;
       // base_certificate::active();
    }

    public function post_install(){

        kernel::single('base_application_manage')->sync();
        //        kernel::set_online(true);
        $rpc_global_server = array(
            'node_id'=> base_mdl_network::MATRIX_ASYNC,
            'node_url'=>config::get('link.matrix_async_url'),
            'node_name'=>'Global Matrix',
            'node_api'=>'',
            'link_status'=>'active',
            );
        app::get('base')->model('network')->replace($rpc_global_server,array('node_id'=> base_mdl_network::MATRIX_ASYNC), true);

		$rpc_realtime_server = array(
                'node_id'=>base_mdl_network::MATRIX_REALTIME,
                'node_url'=>config::get('link.matrix_realtime_url'),
                'node_name'=>'Realtime Matrix',
                'node_api'=>'',
                'link_status'=>'active',
            );

		app::get('base')->model('network')->replace($rpc_realtime_server,array('node_id'=>base_mdl_network::MATRIX_REALTIME), true);

		$rpc_service_server = array(
                'node_id'=>base_mdl_network::MATRIX_SERVICE,
                'node_url'=>config::get('link.matrix_service_url'),
                'node_name'=>'Service Matrix',
                'node_api'=>'',
                'link_status'=>'active',
            );

		app::get('base')->model('network')->replace($rpc_service_server,array('node_id'=>base_mdl_network::MATRIX_SERVICE), true);
    }

    public function post_update($dbinfo)
    {
        $dbver = $dbinfo['dbver'];
        if(empty($dbver) || $dbver < 0.29)
        {
            $configWrite = new base_setup_config();
            $configWrite->overwrite = true;

            $configs = [
                'database.connections.default.host' => config::get('database.host'),
                'database.connections.default.dbname' => config::get('database.database'),
                'database.connections.default.user' => config::get('database.username'),
                'database.connections.default.password' => config::get('database.password'),
            ];

            $configWrite->write($configs);
        }

        

        $rpc_global_server = array(
                'node_id'=> base_mdl_network::MATRIX_ASYNC,
                'node_url'=>config::get('link.matrix_async_url'),
                'node_name'=>'Global Matrix',
                'node_api'=>'',
                'link_status'=>'active',
            );
        app::get('base')->model('network')->replace($rpc_global_server,array('node_id'=> base_mdl_network::MATRIX_ASYNC), true);

		$rpc_realtime_server = array(
                'node_id'=>base_mdl_network::MATRIX_REALTIME,
                'node_url'=>config::get('link.matrix_realtime_url'),
                'node_name'=>'Realtime Matrixi',
                'node_api'=>'',
                'link_status'=>'active',
            );

		app::get('base')->model('network')->replace($rpc_realtime_server,array('node_id'=>base_mdl_network::MATRIX_REALTIME), true);

		$rpc_service_server = array(
                'node_id'=>base_mdl_network::MATRIX_SERVICE,
                'node_url'=>config::get('link.matrix_service_url'),
                'node_name'=>'Service Matrix',
                'node_api'=>'',
                'link_status'=>'active',
            );

		app::get('base')->model('network')->replace($rpc_service_server,array('node_id'=>base_mdl_network::MATRIX_SERVICE), true);

    }//End Function


}

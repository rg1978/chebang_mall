<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_application_manage
{

    public function isBaseInstalled()
    {
        $status = app::get('base')->status();
        switch ($status) {
        case 'installed':
            return true;
        default:
            return false;
        }
    }
    

    //应用程序资源探测器。
    //想添加自己的探测器? 注册服务: app_content_detector
    static function content_detector($app_id=null){
        $content_detectors =  array(
            'list'=>array(
                'base_application_dbtable',
                'base_application_service',
                'base_application_crontab',
                //'base_application_imgbundle',
            )
        );

        if($app_id!='base'){
            $content_detectors_addons = app::get('base')->model('app_content')->getlist('content_path,app_id',array(
                'content_type'=>'service',
                'content_name'=>'app_content_detector',
                'disabled'=>'false',
            ));
            foreach($content_detectors_addons as $row){
                $content_detectors['list'][$row['content_path']] = $row['content_path'];
            }
        }
        return new service($content_detectors);
    }

    public function uninstall_queue($apps){
        if(is_string($apps)){
            $apps = array($apps);
        }
        $rows = app::get('base')->database()->executeQuery('select app_id,app_name from base_apps where status <> "uninstalled"')->fetchAll();
        $depends_apps_map = array();
        foreach($rows as $row){
            $namemap[$row['app_id']] = $row['app_name'];
            $depends_apps = app::get($row['app_id'])->define('depends/app');
            if($depends_apps){
                foreach($depends_apps as $dep_app){
                    $depends_apps_map[$dep_app['value']][] = $row;
                }
            }
        }
        foreach($apps as $app_id){
            $this->check_depends_uninstall($app_id, $depends_apps_map,$queue);
        }
        foreach($apps as $app_id){
            $queue[$app_id] = array($namemap[$app_id],0);
        }
        return $queue;
    }

    public function active_queue($apps)
    {
        if(is_string($apps)){
            $apps = array($apps);
        }
        foreach($apps as $app_id){
            $this->check_active_install($app_id, $queue);
            $queue[$app_id] = app::get($app_id)->define();
        }
        return $queue;
    }//End Function

    private function check_active_install($app_id, &$queue){
        $depends_app = app::get($app_id)->define('depends/app');
        foreach((array)$depends_app as $depend_app_id){
            $this->check_active_install($depend_app_id['value'], $queue);
        }
        if(app::get($app_id)->status() == 'uninstalled' || app::get($app_id)->status() == 'paused'){
            $queue[$app_id] = app::get($app_id)->define();
        }
    }

    public function pause_queue($apps)
    {
        if(is_string($apps)){
            $apps = array($apps);
        }
        $rows = app::get('base')->database()->executeQuery('select app_id,app_name from base_apps where status = "active"')->fetchAll();
        $depends_apps_map = array();
        foreach($rows as $row){
            $namemap[$row['app_id']] = $row['app_name'];
            $depends_apps = app::get($row['app_id'])->define('depends/app');
            if($depends_apps){
                foreach($depends_apps as $dep_app){
                    $depends_apps_map[$dep_app['value']][] = $row;
                }
            }
        }
        foreach($apps as $app_id){
            $this->check_depends_uninstall($app_id, $depends_apps_map,$queue);
        }
        foreach($apps as $app_id){
            $queue[$app_id] = array($namemap[$app_id],0);
        }
        return $queue;
    }//End Function

    private function check_depends_uninstall($app_id,$depends_apps_map, &$queue){
        if(isset($depends_apps_map[$app_id])){
            foreach($depends_apps_map[$app_id] as $to_delete){
                $this->check_depends_uninstall($to_delete['app_id'],$depends_apps_map,$queue);
                $queue[$to_delete['app_id']] = array($to_delete['app_name'],1);
            }
        }
    }

    public function install_queue($apps,$force_install=false){
        if(is_string($apps)){
            $apps = array($apps);
        }

        foreach($apps as $app_id){
            $this->check_depends_install($app_id, $queue);
            if($force_install){
                $queue[$app_id] = app::get($app_id)->define();

            }
        }

        return $queue;
    }

    private function check_depends_install($app_id, &$queue){
        $depends_app = app::get($app_id)->define('depends/app');
        foreach((array)$depends_app as $depend_app_id){
            $this->check_depends_install($depend_app_id['value'], $queue);
        }
        
        if(app::get($app_id)->status() == 'uninstalled'){
            $queue[$app_id] = app::get($app_id)->define();
        }
    }

    protected function checkMainAppForInstall($app) {
        $app_info = $app->define('main_app');
        
        $app_exclusion = app::get('base')->getConf('system.main_app');
        
        if($app_info['value'] == 'true'){
            if($app_info['exclusion'] == 'true'){
                if($app_exclusion['value'] == 'true' && $app_exclusion['exclusion'] == 'true' && $app_exclusion['app_id'] != $app_id){
                    logger::info('Application '.$app_id.' exclusioned '.$app_exclusion['app_id'].'.');
                    exit;
                }
            }
            $app_info['app_id'] = $app_id;
            $app_exclusion = app::get('base')->setConf('system.main_app', $app_info);
        }
    }

    public function install($app_id,$options=null,$auto_enable=1){
        $app = app::get($app_id);

        if ($this->isBaseInstalled()) {
            if (app::get($app_id)->status() !== 'uninstalled') {
                logger::info(sprintf('Application package %s Already installed'), $app_id);
                return false;
            }

            $this->checkMainAppForInstall($app);

            if (app::get($app_id)->status() == 'installing') {
                logger::info('Application %s is installing. Wait for a moment');
                exit;
            }
            
            app::get('base')->model('apps')->update(
                array('status'=>'installing'),
                array('app_id'=>$app_id)
            );

            kernel::single('base_application_dbtable')->clear_by_app($app_id);  //清除冗余表信息
        }

        $app->runtask('pre_install',$options);

        foreach($this->content_detector($app_id) as $detector){
            foreach($detector->detect($app) as $name=>$item){
                $item->install();
            }

            redis::scene('system')->hset('service_last_modified',
                                         get_class($detector).'.'.$app_id,  
                                         $detector->last_modified($app_id));
        }

        //用自己新安装的资源探测器，安装自己的资源
        foreach(kernel::servicelist('app_content_detector') as $k=>$detector){
            if($detector->app->app_id==$app_id){
                //遍历所有已经安装的app
                foreach($detector->detect($app) as $name=>$item){
                    $item->install();
                }

                redis::scene('system')->hset('service_last_modified',
                                             get_class($detector).'.'.$app_id,
                                             $detector->last_modified($app_id));  
            }
        }

        app::get('base')->model('apps')->replace(
            array('status'=>'installed','app_id'=>$app_id, 'dbver'=>$app->define('version'))
            ,array('app_id'=>$app_id)
        );

        $deploy_info = base_setup_config::deploy_info();
        foreach((array)$deploy_info['setting'] as $set){
            if($set['app']==$app_id){
                $app->setConf($set['key'],$set['value']);
            }
        }

        $app->runtask('post_install',$options);

        if($auto_enable){
            $this->enable($app_id);
        }

        $this->update_app_content($app_id);
        logger::info('Application '.$app_id.' installed... ok.');
        // 对本app更新自己提供的服务
        
    }

    
    public function uninstall($app_id){
        $this->disable($app_id);

        $app = app::get($app_id);
        $app->runtask('pre_uninstall');

        //对于BASE, 只要删除数据库即可  删无可删,无需再删
        if ($this->isBaseInstalled() && $app_id =='base') {
            kernel::single('base_application_dbtable')->clear_by_app('base');
        }else{
            foreach($this->content_detector($app_id) as $detector){
                $detector->clear_by_app($app_id);
            }
            app::get('base')->model('app_content')->delete(array('app_id'=>$app_id));

            $app->runtask('post_uninstall');
            /*
            app::get('base')->model('apps')->update(
                array('status'=>'uninstalled')
                ,array('app_id'=>$app_id)
            );
            */
            //todo:应要求暂时在app卸载时把app信息一同抹去，需要手工运行检查更新
            //modify by edwin.lzh@gmail.com 2011/3/24
            app::get('base')->model('apps')->delete(array('app_id'=>$app_id));

            $app_ext = app::get('base')->getConf('system.main_app');
            if($app_id == $app_ext['app_id']){
                app::get('base')->setConf('system.main_app', array());
            }
        }
        logger::info('Application '.$app_id.' removed');
    }

    public function pause($app_id)
    {
        if($app_id == 'base'){
            logger::info('Appication base can\'t be paused');
        }
        else
        {
            $count = app::get('base')->database()->executeQuery('select count(*) from base_apps where app_id = ? AND status = "active"', [$app_id])->fetchColumn();

            if(empty($count))
            {
                logger::info('Application ' . $app_id . ' don\'t be pause');
                return ;
            }
            $this->disable($app_id);
            $app = app::get($app_id);

            foreach($this->content_detector($app_id) as $detector){
                $detector->pause_by_app($app_id);
            }
            app::get('base')->model('app_content')->delete(array('app_id'=>$app_id));

            app::get('base')->model('apps')->update(
                array('status'=>'paused')
                ,array('app_id'=>$app_id)
            );

            logger::info('Application '.$app_id.' paused');
        }
    }//End Function

    public function active($app_id)
    {
        $row = app::get('base')->database()->executeQuery('select status from base_apps where app_id = ? AND status IN ("uninstalled", "paused")', [(string)$app_id])->fetch();
        switch($row['status'])
        {
            case 'paused':
                $this->enable($app_id);
                $app = app::get($app_id);

                foreach($this->content_detector($app_id) as $detector){
                    $detector->active_by_app($app_id);
                }

                //用自己新启用的资源探测器，启用自己的资源
                foreach(kernel::servicelist('app_content_detector') as $k=>$detector){
                    if($detector->app->app_id==$app_id){
                        //遍历所有已经安装的app
                        $detector->active_by_app($app_id);
                    }
                }

                app::get('base')->model('apps')->update(
                    array('status'=>'active')
                    ,array('app_id'=>$app_id)
                );

                logger::info('Application '.$app_id.' actived');
                return;
            case 'uninstalled':
                $this->install($app_id);
                return;
            default:
                logger::info('Application ' . $app_id . ' don\'t be active');
                return ;
        }
    }//End Function

    public function enable($app_id){
        
        $app = app::get($app_id);
        $app->runtask('pre_enable');

        app::get('base')->model('app_content')->update(
            array('disabled'=>0)
            ,array('app_id'=>$app_id)
        );
        app::get('base')->model('apps')->update(
            array('status'=>'active')
            ,array('app_id'=>$app_id)
        );

        logger::info('Application '.$app_id.' actived');
        $app->runtask('post_enable');
    }

    public function disable($app_id){
        $app = app::get($app_id);
        $app->runtask('pre_disable');

        app::get('base')->model('app_content')->update(
            array('disabled'=>1)
            ,array('app_id'=>$app_id)
        );
        app::get('base')->model('apps')->update(
            array('status'=>'installed')
            ,array('app_id'=>$app_id)
        );

        $app->runtask('post_disable');
    }

    public function update_app_content($app_id,$autofix=true){
        foreach($this->content_detector($app_id) as $k=>$detector){
            $last_modified = $detector->last_modified($app_id);

            $current_last_modified = redis::scene('system')->hget('service_last_modified', get_class($detector).'.'.$app_id);
                                                                  
            if ($last_modified != false && $current_last_modified != $last_modified) {
                logger::info('Updating '.$k.'@'.$app_id.'.');
                if($autofix){
                    $detector->update($app_id);

                    redis::scene('system')->hset('service_last_modified',
                                                 get_class($detector).'.'.$app_id,
                                                 $last_modified);
                }
            }
        }
    }

    public function sync(){

        logger::info('Updating Application library..');
        $xmlfile = tempnam(TMP_DIR,'appdb_');
        #kernel::single('base_pget')->dl(config::get('link.url_app_fetch_index'),$xmlfile);

        $appdb = kernel::single('base_xml')->xml2array(
		file_get_contents($xmlfile),'base_app');
        app::get('base')->model('apps')->update(array('remote_ver'=>''));
        if($appdb['app']){
            app::get('base')->model('apps')->delete(array('installed'=>false));
        }

        foreach((array)$appdb['app'] as $app){
            $data = array(
                'app_id'=>$app['id'],
                'app_name'=>$app['name'],
                'remote_ver'=>$app['version'],
                'description'=>$app['description'],
                'author_name'=>$app['author']['name'],
                'author_url'=>$app['author']['url'],
                'author_email'=>$app['author']['email'],
                'remote_config'=>$app,
            );

            app::get('base')->model('apps')->replace($data,array('app_id'=>$app['id']));
        }

        $this->update_local();

        logger::info('Application libaray is updated, ok.');
    }

    private function update_local_app_info($app_id){
        $app = app::get($app_id)->define();
        $data = array(
            'app_id'=>$app_id,
            'app_name'=>$app['name'],
            'local_ver'=>$app['version'],
            'description'=>$app['description'],
            'author_name'=>$app['author']['name'],
            'author_url'=>$app['author']['url'],
            'author_email'=>$app['author']['email'],
        );
        app::get('base')->model('apps')->replace($data,array('app_id'=>$app_id));
    }

    public function update_local(){
        logger::info('Scanning local Applications... ');
        if ($handle = opendir(APP_DIR)) {
            while (false !== ($file = readdir($handle))) {
                if($file{0}!='.' && is_dir(APP_DIR.'/'.$file) && file_exists(APP_DIR.'/'.$file.'/app.xml')){
                    $this->update_local_app_info($file);
                }
            }
            closedir($handle);
        }
        logger::info('Scanning local Applications ok.');
        return $this->_list;
    }
}

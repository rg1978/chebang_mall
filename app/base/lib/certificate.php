<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_certificate{

    static $certi= null;

    static function register($data=null){
        $sys_params = base_setup_config::deploy_info();
        $code = md5(microtime());
        redis::scene('system')->set('net.handshake',$code);
        
        $app_exclusion = app::get('base')->getConf('system.main_app');
        /** 得到框架的总版本号 **/
        $obj_apps = app::get('base')->model('apps');
        $tmp = $obj_apps->getList('*',array('app_id'=>'base'));
        $app_xml = $tmp[0];
        $app_xml['version'] = $app_xml['local_ver'];
        if (defined('CERTIFICATE_SAS') && constant('CERTIFICATE_SAS')){
            $data = array(
                'certi_app'=>'open.reg',
                'app_id' => 'ecos.'.$app_exclusion['app_id'],
                'url' => $data ? $data : kernel::base_url(1),
                'result' => $code,
                'version'=> $app_xml['version'],
            );
        }else{
            $conf = base_setup_config::deploy_info();
            $data = array(
                'certi_app'=>'open.reg',
                'identifier'=>base_enterprise::ent_id(),
                'password'=>base_enterprise::ent_ac(),
                'product_key' => $conf['product_key'],
                'url' => $data ? $data : kernel::base_url(1),
                'result' => $code,
                'version'=> $app_xml['version'],
                'api_ver'=>'1.3',
            );
        }

        $posturl = config::get('link.license_center');
        logger::info("register:".var_export($data, true));

        try {
            $result = client::post($posturl, ['body' => $data, 'timeout'=>6])->json();
        }
        catch (Exception $e) {
            $result = [];
        }

        //todo: 声称获取一个唯一iD，发给飞飞

        logger::info("regist:".var_export($result, true));
        if($result['res']=='succ'){
            if ($result['info'])
            {
                $certificate = $result['info'];
                $flag = self::set_certificate($certificate);
                if( $flag ){
                    app::get('base')->setConf('certificate_code_url',$data['url']);
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            //throw new Exception(config::get('link.license_center')." return ".$result['res']."error is-- ".$result['code'].",".$result['msg']);
            logger::error('create certificate_id faile, reason:'.config::get('link.license_center')." return ".$result['res']."error is ".$result['code'].",".$result['msg'], false, LOG_ERR ) ;
            return false;
        }
    }

    /**
     * 获取证书的版权信息
     * @param string app id
     * @return boolean 成功与否
     */
    static function active_certi_info($app_id='b2c')
    {
        $ceti_app = 'open.certi_info';
        $certi_id = self::certi_id();
        $token = self::token();

        $certi_ac = md5($ceti_app.$certi_id.$token);
        $data = array(
            'certi_app'=>$ceti_app,
            'certificate_id'=>$certi_id,
            'certi_ac'=>$certi_ac,
        );

        $posturl = config::get('link.license_center');

        logger::info("active_certi_info:".var_export($data, true));

        try {
            $result = client::post($posturl, ['body' => $data, 'timeout'=>6])->json();
        }
        catch (Exception $e) {
            $result = [];
        }

        logger::info("active_certi_info:".var_export($result, true));

        if ($result['res'] == 'succ')
        {
            return self::set_certi_info($app_id, json_encode($result['info']));
        }
        else
        {
            kernel::error('Certificate info getting fail, ' . $result['msg']);
            return false;
        }
    }

    /**
     * 设置证书的正确权限信息
     * @param string app id
     * @param mixed certi info
     * @return boolean 成功与否
     */
    static function set_certi_info($app_id, $info)
    {
        if (!$app_id || !$info)
            return false;

        return app::get($app_id)->setConf('certi_info', $info);
    }

    /**
     * 获取证书的版权信息
     * @param string app id
     * @return string key_type
     */
    static function certi_info($app_id='b2c')
    {
        $certi_info = app::get($app_id)->getConf('certi_info');
        $certi_info = json_decode($certi_info, 1);

        return $certi_info['key_type'];
    }

    static function get($code='certificate_id'){

        if(!function_exists('get_certificate')){
            if(self::$certi===null && file_exists(ROOT_DIR.'/config/certi.php') ){
                require(ROOT_DIR.'/config/certi.php');
                self::$certi = $certificate;
            }
        }else{
            self::$certi = get_certificate();
        }

        return self::$certi[$code];
    }

    static function active(){
        if(self::get()){
            logger::info('Using exists certificate: config/certi.php');
        }else{
            logger::info('Request new certificate');
            self::register();
        }
    }


    static function set_certificate($certificate){
        if(!function_exists('set_certificate')){
            return file_put_contents(ROOT_DIR.'/config/certi.php'
                ,'<?php $certificate='.var_export($certificate,1).';');
        }else{
            return set_certificate($certificate);
        }
    }
    static function del_certificate(){
        if(is_file(ROOT_DIR.'/config/certi.php'))
            unlink(ROOT_DIR.'/config/certi.php');
    }
    static function gen_sign($params){
        return strtoupper(md5(strtoupper(md5(self::assemble($params))).self::token()));
    }

    static function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? self::assemble($val) : $val);
        }
        return $sign;
    }//End Function

    static function certi_id(){
        return self::get('certificate_id');
    }

    static function token(){
        return self::get('token');
    }

    static function get_certi_logo_url(){

        $params['certi_app']       = 'open.login';
        $params['certificate_id']  = self::get('certificate_id');
        $params['format'] = 'image';
        /** 增加反查参数result和反查基础地址url **/
        $code = md5(microtime());
        redis::scene('system')->set('net.login_handshake',$code);
        
        $params['result'] = $code;
        /** 得到框架的总版本号 **/
        //$app_xml = kernel::single('base_xml')->xml2array(file_get_contents(app::get('base')->app_dir.'/app.xml'),'base_app');
        $obj_apps = app::get('base')->model('apps');
        $tmp = $obj_apps->getList('*',array('app_id'=>'base'));
        $app_xml = $tmp[0];
        //        $params['version'] = $app_xml['local_ver'];
        $params['url'] = kernel::base_url(1);
        /** end **/
        $str   = '';
        ksort($params);
        foreach($params as $key => $value){
            $str.=$value;
        }
        $params['certi_ac'] = md5($str.self::token());

        $posturl = config::get('link.license_center');

        logger::info("get_certi_logo_url:".var_export($data, true));

        try {
            $result = client::post($posturl, ['body' => $params, 'timeout'=>6])->json();
        }
        catch (Exception $e) {
            $result = [];
        }
        logger::info("get_certi_logo_url:".var_export($result, true));

        if ($result)
        {
            // 存在异常
            if ($result['res'] == 'fail')
            {
                $image_url = $result['msg'];
            }
            else
            {
                if ($result['res'] == 'succ')
                    $image_url = stripslashes($result['info']);
                else
                    $image_url = stripslashes($result);
            }
        }
        else
            $image_url = stripslashes($result);


        return $image_url;
    }
}

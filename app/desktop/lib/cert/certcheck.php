<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

class desktop_cert_certcheck
{
    function __construct($app)
    {
        $this->app = $app;
    }
    function check($app)
    {
        $opencheck = false;
        $objCertchecks = kernel::servicelist("desktop.cert.check");
        foreach ($objCertchecks as $objCertcheck)
        {
            if(method_exists($objCertcheck , 'certcheck') && $objCertcheck->certcheck()){
                $opencheck = true;
                break;
            }
        }
        if(!$opencheck || $this->is_internal_ip() || $this->is_demosite()) return ;

        $activation_arr = app::get('desktop')->getConf('activation_code');
        logger::info('activation_code:'.var_export($activation_arr,1));
        if($activation_arr) return ;
        else
        {
            echo $this->error_view();
            exit;
        }
    }

    function getform()
    {
        $pagedata['res_url'] = app::get('desktop')->res_url;
        $pagedata['auth_error_msg'] = $auth_error_msg;
        return view::make('desktop/active_code_form.html', $pagedata);
    }

    function error_view($auth_error_msg=null)
    {
        $pagedata['res_url'] = app::get('desktop')->res_url;
        $pagedata['auth_error_msg'] = $auth_error_msg;
        return view::make('desktop/active_code.html', $pagedata);
    }
    /**
     *		ocs :
     * 	$method = 'active.do_active'
     *		$ac = 'SHOPEX_ACTIVE'
     *
     *		其它产品默认
     */
    function check_code($code=null,$method='oem.do_active',$ac = 'SHOPEX_OEM')
    {
        if(!$code)return false;
        $certificate_id = base_certificate::certi_id();
        if(!$certificate_id)base_certificate::register();
        $certificate_id = base_certificate::certi_id();
        $token =  base_certificate::token();
        $data = array(
            'certi_app'=>$method,
            'certificate_id'=>$certificate_id,
            'active_key'=>$_POST['auth_code'],
            'ac'=>md5($certificate_id.$ac)
        );
        logger::info("LICENSE_CENTER_INFO:".print_r($data,1));

        try {
            $result = client::post(config::get('link.license_center'), ['body' => $data, 'timeout'=>6])->json();
        }
        catch (Exception $e) {
            $result = [];
        }

        logger::info("LICENSE_CENTER_INFO:".print_r($result,1));
        return $result;
    }

    function check_certid()
    {
        $params['certi_app'] = 'open.login';
        $this->Certi = base_certificate::get('certificate_id');
        $this->Token = base_certificate::get('token');
        $params['certificate_id']  = $this->Certi;
        $params['format'] = 'json';
        /** 增加反查参数result和反查基础地址url **/
        $code = md5(microtime());
        redis::scene('system')->set('net.login_handshake', $code);
        $params['result'] = $code;
        $obj_apps = app::get('base')->model('apps');
        $tmp = $obj_apps->getList('*',array('app_id'=>'base'));
        $app_xml = $tmp[0];
        $params['version'] = $app_xml['local_ver'];
        $params['url'] = kernel::base_url(1);
        /** end **/
        $token = $this->Token;
        $str   = '';
        ksort($params);
        foreach($params as $key => $value){
            $str.=$value;
        }
        $params['certi_ac'] = md5($str.$token);

        $posturl = config::get('link.license_center');
        try {
            $api_arr = client::post($posturl, ['body' => $params, 'timeout'=>20])->json();
        }
        catch (Exception $e) {
            $api_arr = [];
        }
        return $api_arr;
    }

    function listener_login($params)
    {
        $opencheck = false;
        $objCertchecks = kernel::servicelist("desktop.cert.check");
        foreach ($objCertchecks as $objCertcheck)
        {
            if(method_exists($objCertcheck , 'certcheck') && $objCertcheck->certcheck()){
                $opencheck = true;
                break;
            }
        }
        if(!$opencheck || $this->is_internal_ip() || $this->is_demosite()) return ;

        //距离上次成功验证时长不足一周的，不用验证
        $chk_certid_lasttime = app::get('desktop')->getConf('chk_certid_lasttime');
        if($chk_certid_lasttime && (time()-$chk_certid_lasttime)<86400*7){
            return ;
        }

        //预先计算验证失败的次数
        $chk_certid_errtimes = app::get('desktop')->getConf('chk_certid_errtimes');
        $chk_certid_errtimes = intval($chk_certid_errtimes) + 1;

        if($params['type'] === pamAccount::getAuthType('desktop'))
        {
            $result = $this->check_certid();
            if($result['res'] == 'succ' && $result['info']['valid'])
            {
                if( !app::get('base')->getConf('certificate_code_url') )
                    app::get('base')->setConf('certificate_code_url',kernel::base_url(1));

                app::get('desktop')->setConf('chk_certid_errtimes', 0);
                app::get('desktop')->setConf('chk_certid_lasttime', time());
                return ;
            }
            elseif($chk_certid_lasttime && $chk_certid_errtimes < 5 )
            {
                app::get('desktop')->setConf('chk_certid_errtimes', $chk_certid_errtimes);
                return ;
            }
            else
            {
                $pagedata['shopexUrl'] = app::get('base')->getConf('certificate_code_url');
                $pagedata['shopexId'] = base_enterprise::ent_id();
                $pagedata['error_code'] = $result['msg'];
                unset($_SESSION['account'][$params['type']]);
                switch($result['msg']){
                case "invalid_version":
                    $msg = "版本号有误，查看mysql是否运行正常"; break;
                case "RegUrlError":
                    $msg = "你当前使用的域名与激活码所绑定的域名不一致。"; break;
                case "SessionError":
                    $msg = "中心请求网店API失败!请找服务商或自行检测网络，保证网络正常。"; break;
                case "license_error":
                    $msg = "证书号错误!请确认config/certi.php文件真的存在！"; break;
                case "method_not_exist":
                    $msg = "接口方法不存在!"; break;
                case "method_file_not_exist":
                    $msg = "接口文件不存在!"; break;
                case "NecessaryArgsError":
                    $msg = "缺少必填参数!"; break;
                case "ProductTypeError":
                    $msg = "产品类型错误!"; break;
                case "UrlFormatUrl":
                    $msg = "URL格式错误!"; break;
                case "invalid_sign":
                    $msg = "验签错误!"; break;
                default:
                    $msg = null;break;
                }
                if($result == null){
                    $msg = "请检测您的服务器域名解析是否正常！";
                }

                $pagedata['msg'] = ($msg)?$msg:"";
                $pagedata['url'] = $url = url::route('shopadmin');
                $pagedata['res_url'] = app::get('desktop')->res_url;
                $pagedata['code_url'] = url::route('shopadmin', array('app' => 'desktop', 'ctl' => 'code', 'act' => 'error_view'));

                echo  view::make('desktop/codetip.html', $pagedata);exit;
            }
        }
        return ;
    }

    /*
     * 检测当环境是外网demo站点时的跳过激活检测
     */
    function is_demosite(){
        if(defined('DEV_CHECKDEMO') && DEV_CHECKDEMO){
            return true;
        }
    }

    function is_internal_ip()
    {
        $ip = $this->remote_addr();
        if($ip=='127.0.0.1' || $ip=='::1'){
            return true;
        }

        $ip = ip2long($ip);
        $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
        $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
        $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
        return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c;
    }


    function remote_addr()
    {
        if(!isset($GLOBALS['_REMOTE_ADDR_'])){
            $addrs = array();

            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
                foreach( array_reverse( explode( ',',  $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) as $x_f )
                {
                    $x_f = trim($x_f);

                    if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f ) )
                    {
                        $addrs[] = $x_f;
                    }
                }
            }

            $GLOBALS['_REMOTE_ADDR_'] = isset($addrs[0])?$addrs[0]:$_SERVER['REMOTE_ADDR'];
        }
        return $GLOBALS['_REMOTE_ADDR_'];
    }
}

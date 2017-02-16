<?php
/**
 * openapi 主控制器
 * @author zhoumin
 * 2015-06-08
 */
class sysopen_chebang_api_controller {
    /**
     * 验证access_token合法性
     * @param string $access_token
     * @return boolean
     */
    public function auth($access_token){
        $str = kernel::single('sysopen_chebang_crypt_think')->decrypt($access_token, CHEBNAGAPI_TOKEN_KEY);
        if ($str){
            $arrStr = explode(CHEBNAGAPI_TOKEY_EXPLODE, $str);
            //$arrStr = unserialize($str);
            if ($arrStr) {
                $appId = $arrStr['appid'];
                $appSecret = $arrStr['secret'];
                $cp = app::get('sysopen')->model('chebang_partner')->getRow('cp_id',array('app_id'=>$appId,'app_secret'=>$appSecret));
                if ($cp) {
                    /*session_id($arrStr['session_id']);
                    session_start();
                    $temp_session = $_SESSION;
                    kernel::single('base_session')->start();
                    $_SESSION = $temp_session;*/
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * 判断调用的类是否存在
     * @param string $class_name
     * @return boolean
     */
    public function checkClass($class_name){
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/sysopen/lib/apis/chebang/'.str_replace('_','/',$class_name).'.php')){
            $path = CUSTOM_CORE_DIR.'/sysopen/lib/apis/chebang/'.str_replace('_','/',$class_name).'.php';
        }else{
            $path = APP_DIR.'/sysopen/lib/apis/chebang/'.str_replace('_','/',$class_name).'.php';
        }
        if(file_exists($path)){
            return true;
        }else{
            return false;
        }
    }

    //生成token chj
    public function createTokenData($appId, $appSecret, $validTime){
       /* $arr['appid'] = $appId;
        $arr['secret'] = $appSecret;
        $arr['valid_time'] = $valid_time;
        $arr['time'] = time();*/

      	//  session_start();
      	//  $arr['session_id'] = session_id();
        // $token = md5($appId.$appSecret.$valid_time.time());
        // $_SESSION['openApiToken'] = $token;
     	//   return serialize($arr);
		$token = kernel::single('sysopen_chebang_crypt_think')->encrypt($appId.$appSecret.$validTime, CHEBNAGAPI_TOKEN_KEY, $validTime);
        return $token;
    }
}
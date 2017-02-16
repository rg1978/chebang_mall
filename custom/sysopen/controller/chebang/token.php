<?php
/**
 * 生成access_token
 * @author chj
 *
 */
class sysopen_ctl_chebang_token extends sysopen_chebang_api_controller {
    
    /**
     * 根据appid和secret生成access_token
     * @return json string
     */
    public function getToken(){
        header("charset=utf-8");
        $appId = input::get('appId');
        $appSecret = input::get('secret');
        $cp = app::get('sysopen')->model('chebang_partner')->getRow('*',array('app_id'=>$appId,'app_secret'=>$appSecret));
        if ($cp){
            $cpId =  $cp['cp_id'];
            //$_SESSION['openApiTokenCpId'] =  $cpId;
            //取得该cp_id的最新token
            /*$cpToken = app::get('base')->database()->executeQuery("select * from sysopen_chebang_partner_log where cp_id =
                       $cpId order by log_id desc")->fetch();

            $_SESSION['make_token']++;
            if($_SESSION['make_token']>5){
                $res = ['status'=>false,'msg'=>'你的请求太频繁了，请稍后再试...'];
                unset($_SESSION['make_token']);
                return json_encode($res,JSON_UNESCAPED_UNICODE);
            }

            if($cpToken){
                $newToken = $cpToken['token'];
                $firstCpToken = app::get('base')->database()->executeQuery("select * from sysopen_chebang_partner_log where cp_id =
                       $cpId and token='$newToken' order by log_id")->fetch();

                if(($cp['valid_time'] ==0) ||($firstCpToken['created_at']+$cp['valid_time']>time()) ){
                    $data =   array('cp_id'=>$cp['cp_id'],'token'=>$cpToken['token'],'created_at' =>time(),'desc'  =>$appId.'用以前的token:'.$cpToken['token'].'访问');
                    app::get('sysopen')->model('chebang_partner_log')->insert($data);
                    $res = ['status'=>200,'msg'=>'successful','data'=>['token'=>$cpToken['token']]];
                    $_SESSION['openApiToken'] =$cpToken['token'];
                    return json_encode($res,JSON_UNESCAPED_UNICODE);
                }
            }*/
            $tokenData = $this->createTokenData($appId,$appSecret, $cp['valid_time']);
            if($tokenData){
                $data =   array('cp_id'=>$cp['cp_id'],'token'=>$tokenData,'created_at' =>time(),'desc'  =>$appId.'获取token成功!');
                app::get('sysopen')->model('chebang_partner_log')->insert($data);
                $res = ['status'=>200,'msg'=>'successful','data'=>['token'=>$tokenData]];
            }else{
                $data =   array('cp_id'=>$cp['cp_id'],'token'=>'','created_at' =>time(),'desc'  =>$appId.'获取token失败!');
                app::get('sysopen')->model('chebang_partner_log')->insert($data);
                $res = ['status'=>false,'msg'=>'获取token失败,联系管理员'];
            }
            return json_encode($res,JSON_UNESCAPED_UNICODE);

        }else {
            /*$_SESSION['make_token']++;
            if($_SESSION['make_token']>5){
                $res = ['status'=>false,'msg'=>'你的请求太频繁了，请稍后再试...'];
                unset($_SESSION['make_token']);
                return json_encode($res,JSON_UNESCAPED_UNICODE);
            }*/

            $data =   array('cp_id'=>'','token'=>'','created_at' =>time(),'desc'  =>'appid或secret不合法!');
            app::get('sysopen')->model('chebang_partner_log')->insert($data);
            $res=['status'=>false,'msg'=>"不合法的appid或secret"];
        }
        return json_encode($res,JSON_UNESCAPED_UNICODE);
       // return response::json($result);exit;
    }
    
}
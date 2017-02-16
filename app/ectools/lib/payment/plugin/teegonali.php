<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 天工支付（支付宝）具体实现
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_teegonali extends ectools_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '天工收银（支付宝）';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '天工收银（支付宝）';
    /**
     * @var string 支付方式key
     */
    public $app_key = 'teegonali';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'teegonali';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '天工收银（支付宝）';
    /**
     * @var string 货币名称
     */
    public $curname = 'CNY';
    /**
     * @var string 当前支付方式的版本号
     */
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"1");

    /**
     * 校验方法
     * @param null
     * @return boolean
     */
    function is_fields_valiad(){
        return true;
    }


    /**
     * 构造方法
     * @param object 传递应用的app
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);

         //$this->callback_url = $this->app->base_url(true)."/apps/".basename(dirname(__FILE__))."/".basename(__FILE__);
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_teegonali', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
        {
            $this->callback_url = str_replace('http://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "http://" . $this->callback_url;
        }
        else
        {
            $this->callback_url = str_replace('https://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "https://" . $this->callback_url;
        }
        $this->signup_url = 'https://charging.teegon.com/passport/signup';
        $this->submit_url = 'https://api.teegon.com/charge/pay';
        //$this->submit_url = "https://sandbox.99bill.com/gateway/recvMerchantInfoAction.htm";
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function intro(){
        return '<b><h3>'.app::get('ectools')->_('天工收银是上海商派2015年正式推出的专业集成支付平台，致力于为各类用户提供融合、便捷、安全的场景支付服务。').'</h3></b><bR>'.app::get('ectools')->_('商派天工C2B收银平台，集成主流支付渠道，含支付宝、微信、银联等，业务覆盖B2B、B2C、B2B2C、C2C、O2O等各类场景，协助互联网用户或企业快速开展收银业务，拓展收银场景').'</h3></b>';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function admin_intro(){
        return '<div class="division" id="payInfoPad"><img border="0" src="' . $this->app->res_url . '/payments/images/tglogo.gif"><br>'.app::get('ectools')->_('天工收银是上海商派2015年正式推出的专业集成支付平台，致力于为各类用户提供融合、便捷、安全的场景支付服务。商派天工C2B收银平台，集成主流支付渠道，含支付宝、微信、银联等，业务覆盖B2B、B2C、B2B2C、C2C、O2O等各类场景，协助互联网用户或企业快速开展收银业务，拓展收银场景').'<br><br><a style="color: #FF0000" target="_blank" href='.$this->signup_url.'>'.app::get('ectools')->_('立即注册为天工会员').'</a>';
    }
    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment){

        $merId = $this->getConf('mer_id', __CLASS__);
        $ikey = $this->getConf('PrivateKey', __CLASS__);
        $return['order_no'] = $payment['payment_id'];
        $return['payment_id'] = $payment['payment_id'];
        $return['channel'] = 'alipay';
        $return['return_url'] = $this->callback_url;
        $return['amount'] = $payment['cur_money'];
        //$return['amount'] = 0.01;
        $return['subject'] =$payment['item_title'].'...';
        $return['metadata'] = md5($payment['payment_id'].$ikey).','.$payment['payment_id'];
        $return['notify_url'] = $this->callback_url;
        $return['client_ip'] = $_SERVER["REMOTE_ADDR"];
        $return['client_id'] = $merId;
        $return['sign'] = $this->sign($return,$ikey);
        foreach($return as $key=>$val) {
            $this->add_field($key,$val);
        }
        if($this->is_fields_valiad()){
            echo $this->get_html();exit;
        }else{
            return false;
        }
    }

    /**
     * 支付回调的方法
     * @param array 回调参数数组
     * @return array 处理后的结果
     */
    function callback(&$in){
        $ikey = $this->getConf('PrivateKey', __CLASS__);
        $payment_id = explode(',',$in['metadata']);
        $sign = $payment_id['0'];
        $ret = array();
        $ret['payment_id'] = $payment_id['1'];
        $ret['currency'] = 'CNY';
        $ret['money'] = $in['amount'];
        $ret['cur_money'] = $in['amount'];
        $ret['trade_no'] = $in['charge_id'];
        $ret['pay_app_id'] = "teegonali";
        $ret['pay_type'] = 'online';
        $ret['memo'] = 'teegonali';
        $ret['t_payed'] =$in['pay_time'];
        $SafetyKey = md5($ret['payment_id'].$ikey);
        if($SafetyKey == $sign && $in['is_success'] == true){
                    $ret['status'] = 'succ';
        }else{
            $message=app::get('ectools')->_("签名认证失败！");
            $ret['status'] = 'invalid';
        }

        return $ret;
    }
    /**
     * 支付成功回打支付成功信息给支付网关
     */
    function ret_result($money){
        $tgarr = array(
                    array("source_account"=>"main","target_account"=>"main","amount"=> $money),
                );
                $tgreturn = json_encode($tgarr);
                $tgsign = md5($tgreturn.$this->getConf('PrivateKey', __CLASS__));
                header('Teegon-Rsp-Sign: '.$tgsign);
                echo $tgreturn;
                exit;
    }

    /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    function setting(){
        return array(
                'pay_name'=>array(
                        'title'=>app::get('ectools')->_('支付方式名称'),
                        'type'=>'string',
                        'validate_type' => 'required',
                ),
                'mer_id'=>array(
                        'title'=>app::get('ectools')->_('客户号client_id'),
                        'type'=>'string',
                        'validate_type' => 'required',
                ),
                 'PrivateKey'=>array(
                         'title'=>app::get('ectools')->_('私钥client_secret'),
                         'type'=>'string',
                         'validate_type' => 'required',
                 ),
                'order_by' =>array(
                    'title'=>app::get('ectools')->_('排序'),
                    'type'=>'string',
                    'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
                ),
                'support_cur'=>array(
                    'title'=>app::get('ectools')->_('支持币种'),
                    'type'=>'text hidden cur',
                    'options'=>$this->arrayCurrencyOptions,
                ),
                'pay_fee'=>array(
                    'title'=>app::get('ectools')->_('交易费率'),
                    'type'=>'pecentage',
                    'validate_type' => 'number',
                ),
                'pay_desc'=>array(
                    'title'=>app::get('ectools')->_('描述'),
                    'type'=>'html',
                    'includeBase' => true,
                ),
                'pay_type'=>array(
                     'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
                     'type'=>'hidden',
                     'name' => 'pay_type',
                ),
                'status'=>array(
                    'title'=>app::get('ectools')->_('是否开启此支付方式'),
                    'type'=>'radio',
                    'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                    'name' => 'status',
                ),
            );
    }

    /**
     * 签名
     * 生成加密串
     */
    public function sign($para_temp,$ikey){
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->para_filter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->arg_sort($para_filter);
        //生成加密字符串
        $prestr = $this->create_string($para_sort);
        $prestr = $ikey .$prestr . $ikey;
        return strtoupper(md5($prestr));
    }


    private function para_filter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign")continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    private function arg_sort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    private function create_string($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key.$val;
        }


        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }


}

?>

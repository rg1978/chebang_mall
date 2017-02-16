<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 预存款支付功能实现
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_deposit extends ectools_payment_app implements ectools_interface_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '预存款支付';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '预存款支付接口';
    /**
     * @var string 支付方式key
     */
    public $app_key = 'deposit';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'deposit';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '预存款';
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
    public $platform = 'iscommon';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"01");

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/ectools_payment_plugin_deposit', 'callback');
        parent::__construct($app);
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        return "预存款";
    }

    /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){
        return array(
            'pay_name'=>array(
                'title'=>app::get('ectools')->_('支付方式名称'),
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
//             'pay_brief'=>array(
//                 'title'=>app::get('ectools')->_('支付方式简介'),
//                 'type'=>'textarea',
//             ),
            'pay_desc'=>array(
                'title'=>app::get('ectools')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'pay_type'=>array(
                'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
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
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro(){
        return "预存款";
    }

	/**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
	{
        $userId = userAuth::id();
        $fee = $payment['cur_money'];
        $memo = "用户支付订单消费。支付单号：{$payment['payment_id']}";

        $depostRequestParams = array(
            'user_id' => $userId,
            'password' => $payment['deposit_password'],
            'fee'  => $fee,
            'memo' => $memo,
        );

        //判断订单是否被支付过的逻辑
        $objMdlTradePaybill = app::get('ectools')->model('trade_paybill');
        $billList = $objMdlTradePaybill->getList('payment_id,tid,payment,status', ['payment_id'=>$payment['payment_id']]);
        foreach($billList as $bill)
        {
            $tid = $bill['tid'];
            $trade = app::get('ectools')->rpcCall('trade.get',['tid'=>$tid, 'fields'=>'tid,status,pay_type']);

            if(!($trade['status'] == 'WAIT_BUYER_PAY' && $trade['pay_type'] == 'online'))
            {
                throw new LogicException('交易状态异常，请到会员中心查看订单状态');
            }
        }

        logger::info('deposit pay request data : ' . var_export($depostRequestParams, 1));
        $result = app::get('ectools')->rpcCall('user.deposit.pay', $depostRequestParams);
        logger::info('deposit pay reponse data : ' . var_export($result, 1));

        if($result['result'] == true)
        {
            $payResult['payment_id'] = $payment['payment_id'];
            $payResult['account'] = 'deposit';
            $payResult['bank'] = app::get('ectools')->_('预存款');
            $payResult['pay_account'] = '用户';
            $payResult['currency'] = 'CNY';
            $payResult['money'] = $fee;
            $payResult['paycost'] = '0.000';
            $payResult['cur_money'] = $fee;
            $payResult['trade_no'] = $payment['payment_id'];
            $payResult['t_payed'] = time();
            $payResult['pay_app_id'] = "deposit";
            $payResult['pay_type'] = 'online';
            $payResult['memo'] = $memo;
            $payResult['status'] = 'succ';
            $payResult['sign'] = md5($this->linkString($payResult));

            $url = $this->notify_url;
            //$res = kernel::single('base_httpclient')->post($url,$payResult);

            //这里之前是同步的，但是由于某些服务器太慢，所以塞队列了。
            system_queue::instance()->publish('ectools_tasks_deposit_payment', 'ectools_tasks_deposit_payment', $payResult);
            //这里请求多次。如果某次请求失败了，将会重新发起请求。
            //避免某次因为网络抖动等因素导致的不稳定的扣掉预存款但是订单状态没有变更。
        //  for($i=0; $i<3; $i++)
        //  {

        //      try
        //      {
        //          $res = client::post($url, ['body' => $payResult, 'timeout' => 6]);
        //      }
        //      catch(Exception $e)
        //      {
        //          if($i == 2)
        //          {
        //              throw $e;
        //          }
        //          continue;
        //      }
        //      break;
        //  }
        }

        $this->submit_url = $url;
        $this->submit_method = 'post';
        $this->submit_charset = 'utf-8';
        foreach($payResult as $key=>$value)
        {
            $this->add_field($key, $value);
        }

        echo $this->get_html();exit;


      //if(base_mobiledetect::isMobile() && $_COOKIE['browse'] != 'pc' )
      //{
      //    redirect::action('topm_ctl_paycenter@finish', ['payment_id'=>$payment['payment_id']])->send();
      //}
      //else
      //{
      //    redirect::action('topc_ctl_paycenter@finish', ['payment_id'=>$payment['payment_id']])->send();
      //}

      //exit;
    }

    /**
	 * 校验方法
	 * @param null
	 * @return boolean
	 */
    public function is_fields_valiad(){
        return true;
    }

	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv)
	{
        if($this->is_return_vaild($recv))
        {
            return $recv;
        }
        else
        {
            throw new LogicException('deposit sign failed');
        }
        return false;
    }

	/**
	 * 生成支付表单 - 自动提交
	 * @params null
	 * @return null
	 */
    public function gen_form()
	{
        return '';
    }

    public function linkString($form)
    {
        $key = base_certificate::token();
        ksort($form);
        foreach($form as $k=>$v){
            if($k!='sign'&&$k!='sign_type'){
                $signstr .= "&$k=$v";
            }
        }

        $signstr = ltrim($signstr,"&");
        $signstr = $signstr.$key;

        return $signstr;
    }

    /**
     * 检验返回数据合法性
     * @param mixed $form 包含签名数据的数组
     * @param mixed $key 签名用到的私钥
     * @access private
     * @return boolean
     */
    public function is_return_vaild($form)
	{
        $signstr = $this->linkString($form);
        if($form['sign']==md5($signstr)){
            return true;
        }
        #记录返回失败的情况
        logger::error(app::get('ectools')->_('支付单号：') . $form['out_trade_no'] . app::get('ectools')->_('签名验证不通过，请确认！')."\n");
        logger::error(app::get('ectools')->_('本地产生的加密串：') . $signstr);
        logger::error(app::get('ectools')->_('预存款传递打过来的签名串：') . $form['sign']);

        return false;
    }

}

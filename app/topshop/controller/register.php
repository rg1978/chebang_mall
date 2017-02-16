<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_register extends topshop_controller {

    public $nomenu = true;
    const NO_MAINLAND = 2;

    //这里引用了模板，使用的是找回密码的页面模板
    public function __construct($app)
    {
        $this->set_tmpl('pwdfind');
        parent::__construct($app);
    }

    //注册时手机验证页面
    public function signCheckPhonePage()
    {
        $pagedata=[];
        return $this->page('topshop/register/signCheckPhonePage.html', $pagedata);
    }

    //根据验证码验证手机号码
    public function signCheckPhoneAction()
    {
        $args = input::get();
        $mobile = $args['mobile'];
        $verify_code = $args['verify_code'];
        try{
            if (! userVcode::verify ($verify_code, $mobile, 'signup'))
                throw new LogicException(app::get('sysshop')->_('验证码验证错误'));

            $this->store('mobile', $mobile);

        }catch(Exception $e){
            return $this->splash('error',$url,$e->getMessage(),true);
        }

        $url = url::action('topshop_ctl_register@signPage');
        return $this->splash('success',$url,$msg,true);
    }

    // 发送验证码
    public function sendSms()
    {
        $request = input::get();
        try
        {
            //检查验证码
            $vcodeType = $request['imagevcodekey'];
            $vcode = $request['vcode'];
            if(!base_vcode::verify($vcodeType,$vcode))
            {
                throw new RuntimeException(app::get('sysshop')->_("图片验证码错误!"));
            }

            // 查看验证类型
            $mobile = $request ['auth_info'];
            // 验证手机号
            $validator = validator::make ([
                'auth_mobile' => $mobile
            ], [
                'auth_mobile' => 'required|mobile'
            ], [
                'auth_mobile' => '手机号必填|手机格式不正确'
            ]);
            $validator->newFails ();

            //判断手机号是否存在
            $authInfo = shopAuth::getFindAuthInfo (['mobile'=>$mobile]);
            if($authInfo['seller_id'])
                throw new LogicException(app::get('sysshop')->_('用户已存在，请直接登录'));


            $flag = userVcode::send_sms ('signup', $mobile);
            if(!$flag)
                throw new RuntimeException(app::get('sysshop')->_('验证码发送失败'));
        } catch ( Exception $e )
        {
            $msg = $e->getMessage ();
            return $this->splash ('error', null, $msg, true);
        }

        $msg = '验证码发送成功，请注意查收';
        return $this->splash ('success', null, $msg, true);
    }


    //登录账号注册页面
    public function signPage()
    {
        $mobile = $this->fetch('mobile');
        if(!$mobile)
            return redirect::action('topshop_ctl_register@signCheckPhonePage');

        $pagedata['mobile'] = $mobile;
        $_SESSION['register']['mobile'] = $mobile;
        $pagedata['license'] = app::get('sysshop')->getConf('sysshop.register.setting_sysshop_license');
        return $this->page('topshop/register/signPage.html', $pagedata);
    }

  ////登录账号注册认证
  //public function signAction()
  //{
  //    return $this->splash('success',$url,$msg,true);
  //}

    //入驻协议
    public function enterAgreementPage()
    {
        $pagedata['loginFlag'] = pamAccount::getAccountId() ?  true : false;
        $pagedata['content'] = app::get('sysshop')->getConf('setprotocol');
        return $this->page('topshop/register/enterAgreementPage.html', $pagedata);
    }

    //输入公司信息
    public function enterProcessCompanyInfo()
    {
        $companyInfo = $this->fetch('companyInfo');
        $companyInfo['shop_info']['establish_date'] = $this->date2time($companyInfo['shop_info']['establish_date']);
        $companyInfo['shop_info']['license_indate'] = $this->date2time($companyInfo['shop_info']['license_indate']);

        $pagedata = $companyInfo;
        $pagedata['applydata'] = $companyInfo;
        $pagedata['resUrl'] = app::get('topshop')->res_url;
        $pagedata['loginFlag'] = pamAccount::getAccountId() ?  true : false;
        return $this->page('topshop/register/enterProcessCompanyInfo.html', $pagedata);
    }

    //公司法人基本信息保存
    public function enterProcessCompanyInfoAction()
    {
        $companyInfo = input::get();

        //数据验证
        $validator = validator::make ([
            'shop_info.corporate_identity_img_z' => $companyInfo['shop_info']['corporate_identity_img_z'],
            'shop_info.corporate_identity_img_f' => $companyInfo['shop_info']['corporate_identity_img_f'],
            'shop_info.license_img' => $companyInfo['shop_info']['license_img'],
            'shop_info.tissue_code_img' => $companyInfo['shop_info']['tissue_code_img'],
            ], [
            'shop_info.corporate_identity_img_z' => 'required',
            'shop_info.corporate_identity_img_f' => 'required',
            'shop_info.license_img' => 'required',
            'shop_info.tissue_code_img' => 'required',
            ], [
            'shop_info.corporate_identity_img_z' => app::get('topshop')->_('请上传身份证正面照片'),
            'shop_info.corporate_identity_img_f' => app::get('topshop')->_('请上传身份证反面照片'),
            'shop_info.license_img' => app::get('topshop')->_('请上传营业执照照片'),
            'shop_info.tissue_code_img' => app::get('topshop')->_('请上传组织机构代码照片'),
            ]);
        try{
            $validator->newFails ();
        }catch(Exception $e) {
            return $this->splash('error',null,$e->getMessage(),true);
        }




        $this->store('companyInfo', $companyInfo);

        $msg = app::get('topshop')->_('保存成功');
        $url = url::action('topshop_ctl_register@enterProcessEconomicInfo');
        return $this->splash('success',$url,$msg,true);
    }

    //银行税务信息
    public function enterProcessEconomicInfo()
    {
        $economicInfo = $this->fetch('economicInfo');
        $pagedata = $economicInfo;
        $pagedata['resUrl'] = app::get('topshop')->res_url;
        $pagedata['loginFlag'] = pamAccount::getAccountId() ?  true : false;
        return $this->page('topshop/register/enterProcessEconomicInfo.html', $pagedata);
    }

    //银行税务信息保存
    public function enterProcessEconomicInfoAction()
    {
        $economicInfo = input::get();

        //数据验证
        $validator = validator::make ([
            'shop_info.tax_code_img' => $economicInfo['shop_info']['tax_code_img'],
            ], [
            'shop_info.tax_code_img' => 'required',
            ], [
            'shop_info.tax_code_img' => app::get('topshop')->_('请上传税务登记证照片'),
            ]);
        try{
            $validator->newFails ();
        }catch(Exception $e) {
            return $this->splash('error',null,$e->getMessage(),true);
        }

        $this->store('economicInfo', $economicInfo);

        $msg = app::get('topshop')->_('保存成功');
        if($economicInfo['action'] == 'back')
            $url = url::action('topshop_ctl_register@enterProcessCompanyInfo');
        else
            $url = url::action('topshop_ctl_register@enterProcessShopInfo');
        return $this->splash('success',$url,$msg,true);
    }

    //店铺信息配置页面
    public function enterProcessShopInfo()
    {
        $shopInfo = $this->fetch('shopInfo');
        $pagedata = $shopInfo;
        $pagedata['applydata'] = $pagedata;
        $pagedata['resUrl'] = app::get('topshop')->res_url;

      $lv1Catlists = app::get('topshop')->rpcCall('category.cat.get.info',array('fields'=>'cat_id,cat_name','parent_id'=>0,'level'=>1));
        foreach($lv1Catlists as $val)
        {
            $catlists[$val['cat_id']] = $val['cat_name'];
        }
        $pagedata['catlist'] = $catlists;

        $shopTypelist = app::get('topshop')->rpcCall('shop.type.get');
        $pagedata['shoptypelist'] = $shopTypelist;
        $pagedata['loginFlag'] = pamAccount::getAccountId() ?  true : false;

        return $this->page('topshop/register/enterProcessShopInfo.html', $pagedata);
    }

    //店铺信息配置保存
    public function enterProcessShopInfoAction()
    {
        $shopInfo = input::get();

        //数据验证
        $vdata = [
            'shop_info.shopuser_identity_img_z' => $shopInfo['shop_info']['shopuser_identity_img_z'],
            'shop_info.shopuser_identity_img_f' => $shopInfo['shop_info']['shopuser_identity_img_f'],
            ];
        $vrule = [
            'shop_info.shopuser_identity_img_z' => 'required',
            'shop_info.shopuser_identity_img_f' => 'required',
            ];
        $verro = [
            'shop_info.shopuser_identity_img_z' => app::get('topshop')->_('请上传身份证正面照片'),
            'shop_info.shopuser_identity_img_f' => app::get('topshop')->_('请上传身份证反面照片'),
            ];
        if(in_array($shopInfo['shop_type'], ['flag', 'brand', 'self']))
        {
            $vdata['shop_info.brand_warranty'] = $shopInfo['shop_info']['brand_warranty'];
            $vrule['shop_info.brand_warranty'] = 'required';
            $verro['shop_info.brand_warranty'] = app::get('topshop')->_('请上传品牌授权书电子版');
        }
        $validator = validator::make ($vdata, $vrule, $verro);
        try{
            $validator->newFails ();

            if($shopInfo['shop_type'] == 'self')
                throw new LogicException(app::get('topshop')->_('店铺类型错误'));

        }catch(Exception $e) {
            return $this->splash('error',null,$e->getMessage(),true);
        }

        $this->store('shopInfo', $shopInfo);

        if($shopInfo['action'] == 'back')
        {
            $url = url::action('topshop_ctl_register@enterProcessEconomicInfo');
            $msg = app::get('topshop')->_('保存成功');
            return $this->splash('success',$url,$msg,true);
        }
        else
        {

            $companyInfo = $this->fetch('companyInfo');
            $economicInfo = $this->fetch('economicInfo');
            $shopInfo = input::get();
            $shop = $shopInfo['shop'];
            $shop_info = array_merge($companyInfo['shop_info'], array_merge($economicInfo['shop_info'], $shopInfo['shop_info']));
            $post = array_merge($companyInfo, array_merge($economicInfo, $shopInfo));
            $post['shop'] = $shop;
            $post['shop_info'] = $shop_info;

            try{
                $this->__checkpost($post);
                $result = app::get('topshop')->rpcCall('shop.create.enterapply',$post);
                $msg = app::get('topshop')->_('申请入驻成功');
                $url = url::action('topshop_ctl_register@enterProcessWaiteExamine');
                $this->__cleanStore();
                return $this->splash('success',$url,$msg,true);
            } catch (\LogicException $e) {
                return $this->splash('error',null,$e->getMessage(),true);
            }

        }
        $url = url::action('topshop_ctl_register@enterProcessWaiteExamine');
        $pagedata['loginFlag'] = pamAccount::getAccountId() ?  true : false;
        return $this->splash('success',$url,$msg,true);
    }

    //编辑页面
    public function enterProcessUpdateApply()
    {
        $datalist = app::get('topshop')->rpcCall('shop.get.enterapply',array('seller_id'=>$this->sellerId,'fields'=>'*'));
        $datalist['shop'] = unserialize($datalist['shop']);
        $datalist['shop_info'] = unserialize($datalist['shop_info']);
        $datalist['shop_info']['establish_date'] = date('Y-m-d', $datalist['shop_info']['establish_date']);
        $datalist['shop_info']['license_indate'] = date('Y-m-d', $datalist['shop_info']['license_indate']);

        $this->store('companyInfo', $datalist);
        $this->store('economicInfo', $datalist);
        $this->store('shopInfo', $datalist);

        return redirect::action('topshop_ctl_register@enterProcessCompanyInfo');
    }

    //等待审核提示页面
    public function enterProcessWaiteExamine()
    {
        $sellerId = pamAccount::getAccountId();
      //$datalist = app::get('topshop')->rpcCall('shop.get.enterapply',array('seller_id'=>$sellerId,'fields'=>'enterlog'));
      //$datalist['enterlog'] = unserialize($datalist['enterlog']);
      //$pagedata['logdata']=$datalist['enterlog'];
        $pagedata['loginFlag'] = pamAccount::getAccountId() ?  true : false;
        return $this->page('topshop/register/enterProcessWaiteExamine.html', $pagedata);
    }

    //等待签约提示页面
    public function enterProcessWaiteAward()
    {
        $sellerId = pamAccount::getAccountId();
        $datalist = app::get('topshop')->rpcCall('shop.get.enterapply',array('seller_id'=>$sellerId,'fields'=>'enterlog,status'));
        $datalist['enterlog'] = unserialize($datalist['enterlog']);
        $pagedata['logdata']=$datalist['enterlog'];
        $pagedata['data'] = $datalist;
        $pagedata['loginFlag'] = pamAccount::getAccountId() ?  true : false;
        return $this->page('topshop/register/enterProcessWaiteAward.html', $pagedata);
    }

    protected function store($key, $value)
    {
        $_SESSION['register'][$key] = $value;
        return true;
    }

    protected function fetch($key)
    {

        return $_SESSION['register'][$key];
    }

    private function __cleanStore()
    {
        $_SESSION['register'] = null;
        return true;
    }

    /**
     * @brief  检测输入项
     *
     * @param $postdata
     *
     * @return array
     */
    private function __checkpost(&$postdata)
    {

        if(!$postdata['seller_id']) $postdata['seller_id'] = $this->sellerId;
        if(!$postdata['status']) $postdata['status'] = 'active';
        $postdata['enterlog'] = array(array(
            'plan'=>$postdata['enterapply_id'] ? '重新提交入驻申请' : '提交入驻申请',
            'times'=>time(),
            'hint'=>$postdata['enterapply_id'] ? '您修改了入驻申请并重新提交' : '您提交了入驻申请',
        ));

        $postdata['shop_info']['establish_date'] = strtotime($postdata['shop_info']['establish_date']);
        $postdata['shop_info']['license_indate'] = strtotime($postdata['shop_info']['license_indate']);

        if($postdata['shop_info']['license_indate'] > 9999999999)
        {
            $msg = app::get('topshop')->_("营业期限不能大于2286-01-01");
            throw new \LogicException($msg);
        }

        $postdata['add_time'] = time();

        if(!$postdata['shop_type'])
        {
            $msg = app::get('topshop')->_("店铺类型不能为空");
            throw new \LogicException($msg);
        }

        if(!array_filter($postdata['shop']['shop_cat']))
        {
            $msg = app::get('topshop')->_("类目不能为空");
            throw new \LogicException($msg);
        }

        if($postdata['shop_name'])
        {
            $params = array(
                'shop_name'=>$postdata['shop_name'],
                'enterapply_id' => $postdata['enterapply_id'],
            );
            try{
                app::get('topshop')->rpcCall('shop.name.check',$params);
            }
            catch(LogicException $e)
            {
                throw new \LogicException($e->getMessage());
            }
        }
        if(mb_strlen(trim($postdata['shop']['shop_descript']),'utf8') > 200)
        {
            $msg = app::get('topshop')->_("店铺描述不能高于200字符");
            throw new \LogicException($msg);
        }

        if(!in_array($postdata['shop_type'],['cat','store']) && !$postdata['new_brand'] && !$postdata['shop']['shop_brand'])
        {
            $msg = app::get('topshop')->_("请选择店铺品牌 或 新增店品牌");
            throw new \LogicException($msg);
        }

        //护照判断
        if($postdata['shop_info']['is_mainland'] == self::NO_MAINLAND)
        {
            if(!$postdata['shop_info']['passport_number'])
            {
                $msg = app::get('topshop')->_("请填写护照号码");
                throw new \LogicException($msg);
            }
        }

        if($postdata['new_brand'])
        {
            $postdata['shop']['postdata_brand'] = "";
        }

        app::get('topshop')->rpcCall('shop.check.brand.sign',$postdata);
        if($postdata['shop_type'] == "cat")
        {
            $postdata['shop']['postdata_brand'] = "";
            $postdata['new_brand'] = "";
        }

        // 判断资料上传
        $this->__checkPostImg($postdata['shop_info'],$postdata['shop_type']);
    }

    // 验证商家入住申请时必上传的图片，只做是否上传判断
    private function __checkPostImg($shopInfo,$shopType)
    {
        if(!$shopInfo)
        {
            return false;
        }
        // 设置必上传的图片数组
        $imgInfo = [
                'corporate_identity_img_z' => app::get('topshop')->_("请上传法人身份证正面复印件"),
                'corporate_identity_img_f' => app::get('topshop')->_("请上传法人身份证反面复印件"),
                'license_img' => app::get('topshop')->_("请上传营业执照副本复印件"),
                'tissue_code_img' => app::get('topshop')->_("请上传组织机构代码证复印件"),
                'tax_code_img' => app::get('topshop')->_("请上传税务登记证复印件"),
                'brand_warranty' => app::get('topshop')->_("请上传品牌授权书电子版"),
                'shopuser_identity_img_z' => app::get('topshop')->_("请上传店主身份证电子版正面"),
                'shopuser_identity_img_f' => app::get('topshop')->_("请上传店主身份证电子版反面")
        ];

        if($shopType == "cat" || $shopType == "store")
        {
            unset($imgInfo['brand_warranty']);
        }

        foreach ($shopInfo as $key => $val)
        {
            if(array_key_exists($key, $imgInfo) && !$val)
            {
                throw new \LogicException($imgInfo[$key]);
            }
        }

        return true;
    }

    private function date2time($date)
    {
        $args = explode('-', $date);

        $time = mktime(0,0,0,$args[1],$args[2],$args[0]);
        return $time;
    }


}


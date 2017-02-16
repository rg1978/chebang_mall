<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_im_webcall extends topshop_controller
{

    public function __construct($app)
    {
        if(!app::get('sysim')->is_installed())
            return kernel::abort(404);
        parent::__construct($app);
    }

    //加载配置参数
    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('365webcall配置');

        $wc = app::get('topshop')->rpcCall('im.shop.webcall.get', ['shop_id'=>$this->shopId, 'fields'=>'*']);

        $pagedata['wc'] = $wc;
        if (!(count($wc) || input::get('flag')))
            return redirect::action('topshop_ctl_im_webcall@applyPage');

        return $this->page('topshop/im/webcall/index.html', $pagedata);
    }

    //保存配置参数
    public function save()
    {
        $url = url::action('topshop_ctl_im_webcall@index');
        $post = specialutils::filterInput(input::get());

        $shopId = $this->shopId;
        $email = $post['email'];

        $requestParams = [];
        $requestParams['shop_id'] = $this->shopId;
        $requestParams['email'] = $post['email'];
        $requestParams['use_im'] = $post['use_im'];

        try{
            app::get('topshop')->rpcCall('im.shop.webcall.edit', $requestParams);
        }catch(Exception $e){
            return $this->splash('error',$url, $e->getMessage(),true);
        }
        return $this->splash('success',$url,'修改成功!',true);
    }

    public function applyPage()
    {
        return $this->page('topshop/im/webcall/apply.html');
    }

    public function apply()
    {
        $wc = specialutils::filterInput(input::get());
        $wc['shop_id'] = $this->shopId;
        try{
            $validator = validator::make(
                ['name'=>$wc['name'], 'email'=>$wc['email'], 'pwd'=>$wc['pwd']],
                ['name'=>'required', 'email'=>'required|email', 'pwd'=>'required'],
                ['name'=>app::get('topshop')->_('昵称不能为空'), 'email'=>app::get('topshop')->_('邮箱不能为空').'|'.app::get('topshop')->_('邮箱格式错误'), 'pwd'=>app::get('topshop')->_('密码不能为空')]
            );
            if($validator->fails())
            {
                $messages = $validator->messages();
                throw new LogicException($messages->first());
            }

            app::get('topshop')->rpcCall('im.shop.webcall.apply', $wc);
        }catch(Exception $e){
            $url = url::action('topshop_ctl_im_webcall@applyPage');
            return $this->splash('error',$url, $e->getMessage(),true);
        }
        $url = url::action('topshop_ctl_im_webcall@index');
        return $this->splash('success',$url,'申请成功!',true);
    }

}



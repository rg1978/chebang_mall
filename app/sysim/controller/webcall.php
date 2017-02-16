<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysim_ctl_webcall extends desktop_controller
{

    /**
     * webcall 列表。finder
     */
    public function index()
    {
        $agentUrl = config::get('im.365webcall.agentlogin');

        return $this->finder('sysim_mdl_account_webcall_shop', array(
            'title' => app::get('sysim')->_('365webcall列表'),
            'use_buildin_delete'=>false,
            'actions' => array(
                array(
                    'label'=>app::get('sysuser')->_('配置平台账号'),
                    'target'=>'dialog::{ title:\''.app::get('sysuser')->_('配置平台365webcall账号').'\', width:400, height:500}',
                    'href'=>'?app=sysim&ctl=webcall&act=platformConfig',
                ),
                array(
                    'label'=>app::get('sysuser')->_('代理商账号登陆'),
                    'target'=>'__blank',
                    'href'=>$agentUrl . '?',
                ),
            ),
        )
    );
    }

    public function platformConfig()
    {
        $shop_id = 'platform';
        $fields = '*';

        $webcallMdl = app::get('sysim')->model('account_webcall_shop');
        $wc = $webcallMdl->getRow($fields, ['shop_id'=>$shop_id]);

        $pagedata['wc'] = $wc;
        $pagedata['flag'] = count($wc) || input::get('flag') ? true : false;
        $pagedata['flag'] = input::get('sign') ? false : $pagedata['flag'];
        return $this->page('sysim/webcall/platformConfig.html', $pagedata);
    }

    public function platformAccountApply()
    {
        $data = input::get();
        $wc = $data['wc'];
        try{
            $shop_id = 'platform';
            $email   = $wc['email'];
            $pwd     = $wc['pwd'];
            $name    = $wc['name'];
            $url     = $wc['url'];
            $area    = $wc['area'];
            $corpName= $wc['corpName'];
            $phone   = $wc['phone'];
            $qq      = $wc['qq'];
            $contact = $wc['contact'];
            $useIm   = $wc['use_im'];

            kernel::single('sysim_webcall_webcall')
                ->addAccount($shop_id, $email, $pwd, $name, $url, $area, $corpName, $phone, $qq, $contact, $useIm);
        }catch(Exception $e){
            return $this->splash('error',null,$e->getMessage());
        }
        return $this->splash('success',null,app::get('sysim')->_("申请成功，请登录代理商账户审核"));
    }

    public function platformConfigSave()
    {
        $data = input::get();
        $wc = [];
        $wc['shop_id'] = 'platform';
        $wc['email']   = $data['wc']['email'];
        $wc['use_im']  = $data['wc']['use_im'];

        try{
            $webcallMdl = app::get('sysim')->model('account_webcall_shop');
            $webcallMdl->save($wc);
        }catch(Exception $e){
            return $this->splash('error',null,$e->getMessage());
        }
        return $this->splash('success',null,app::get('sysim')->_("保存成功"));
    }

}

<?php

class sysopen_ctl_admin_chebang_partner extends desktop_controller {
    public $workground = 'sysopen.wrokground.shop';

    function __construct($app){
        parent::__construct($app);
        $this->mdlPartner = app::get('sysopen')->model('chebang_partner');
    }

    /**
     * @brief  合作伙伴列表
     *
     * @return
     */
    public function index()
    {
        return $this->finder('sysopen_mdl_chebang_partner',array(
            'title' => app::get('sysopen')->_('合作伙伴列表'),
            'actions' =>array(
                array(
                    'label'=>app::get('sysopen')->_('新建'),
                    'href'=>'?app=sysopen&ctl=admin_chebang_partner&act=create',
                    'target'=>'dialog::{title:\''.app::get('sysopen')->_('新建').'\',width:400,height:300}'
                ),
            ),
            'use_buildin_new_dialog' => true,
            'use_buildin_filter' => true,
        ));
    }

    public function create($cpid)
    {
        if( $cpid )
        {
            $cpInfo = $this->mdlPartner->getRow('*',array('cp_id'=>$cpid));
            $pagedata['cpInfo'] = $cpInfo;
        }

        return view::make('sysopen/admin/chebang/addPartner.html', $pagedata);
    }


    public function savePartner()
    {
        //$this->begin('?app=sysopen&ctl=admin_partner&act=index');
        $this->begin();
        $data = input::get();
        if (empty($data['cp_id'])) {

            $saveData['cp_name'] = trim($data['cp_name']);
            $saveData['app_id'] = $data['app_id'];
            $saveData['app_secret'] = $data['app_secret'];
            $saveData['addtime'] = time();
            $saveData['valid_time'] = $data['valid_time'];
            //logger::info("[sysopen_ctl_admin_partner.savepartner] " .json_encode($saveData));
            $cp = $this->mdlPartner->getRow('cp_id',array('app_id'=>$data['app_id']));
            if(!$cp){
                if($this->mdlPartner->insert($saveData)){
                    $this->end(true, app::get('sysopen')->_('新建成功'));
                }
                else
                {
                    $this->end(false, app::get('sysopen')->_('新建失败'));
                }
            }else{
                $this->end(false, app::get('sysopen')->_('appid不能重复'));
            }
        }
        else
        {
            $filter = array('cp_id' => $data['cp_id']);
            //$saveData['cp_id'] = $data['cp_id'];
            $saveData['cp_name'] = trim($data['cp_name']);
            $saveData['app_id'] = $data['app_id'];
            $saveData['app_secret'] = $data['app_secret'];
            $saveData['addtime'] = time();
            $saveData['valid_time'] = $data['valid_time'];
            //logger::info("[sysopen_ctl_admin_partner.savepartner] " .json_encode($saveData));
            $cp = $this->mdlPartner->getRow('cp_id',array('app_id'=>$data['app_id'],'cp_id|noequal'=>$data['cp_id']));
            if(!$cp) {
                if ($this->mdlPartner->update($saveData, $filter)) {
                    $this->end(true, app::get('sysopen')->_('编辑成功'));
                } else {
                    $this->end(false, app::get('sysopen')->_('编辑失败'));
                }
            }else{
                $this->end(false, app::get('sysopen')->_('appid不能重复'));
            }

        }
   }
}
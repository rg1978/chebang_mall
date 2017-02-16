<?php
class sysopen_ctl_admin_develop extends desktop_controller {

    public function index()
    {
        return $this->finder('sysopen_mdl_develop',array(
            'actions'=>array(
                array(
                    'label'=>app::get('sysuser')->_('新增开发者账号'),
                    'target'=>'dialog::{ title:\''.app::get('sysuser')->_('新增一个开发者账号').'\', width:400, height:200}',
                    'href'=>'?app=sysopen&ctl=admin_develop&act=addView',
                ),
            ),
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'title' => app::get('systrade')->_('开发者列表'),
            'use_buildin_delete'=>false,
        ));
    }

    public function addView()
    {
        return $this->page('sysopen/develop/add.html');
    }

    public function doCreate()
    {
        $name = trim($_POST['name']);
        if( empty($name) )
        {
            $msg = app::get('sysopen')->_('开发者名称必填');
            return $this->splash('error',null,$msg);
        }

        try
        {
            $developId = kernel::single('sysopen_key')->create($name);
        }
        catch( Exception $e )
        {
            $msg = app::get('sysopen')->_('创建失败，请保证和prism配置正确');
            return $this->splash('error',null,$msg);
        }

        $this->adminlog("新建开发者账号[开发者ID:{$developId}", 1);

        return $this->splash('success',null,"新建开发者账号成功");
    }
}

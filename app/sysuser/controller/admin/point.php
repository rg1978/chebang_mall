<?php
class sysuser_ctl_admin_point extends desktop_controller{

    public function index()
    {
        $userId = intval(input::get('user_id'));
        return $this->finder('sysuser_mdl_user_pointlog',array(
            'title' => app::get('sysuser')->_('会员积分明细'),
            'base_filter' => array('user_id' => $userId),
            'use_buildin_delete'=>false,
        ));
    }

    public function toSetting()
    {
    }

    public function doSetting()
    {
    }
}

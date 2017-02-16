<?php
class syslogistics_ctl_admin_delivery extends desktop_controller{
    public function index()
    {
        return $this->finder('syslogistics_mdl_delivery',array(
            'use_buildin_delete' => false,
            'title' => app::get('syslogistics')->_('发货单列表'),
        ));
    }

    public function _views()
    {
        $sub_menu = array(
            0=>array(
                'label'=>app::get('systrade')->_('全部'),
                'optional'=>false,
            ),
            1=>array(
                'label'=>app::get('systrade')->_('发货成功'),
                'optional'=>false,
                'filter'=>array(
                    'status'=>'succ'
                )
            ),
            2=>array(
                'label'=>app::get('systrade')->_('发货失败'),
                'optional'=>false,
                'filter'=>array(
                    'status'=>'failed'
                )
            ),
        );
        return $sub_menu;
    }
}

<?php
class ectools_ctl_admin_tradepayment extends desktop_controller{

    public function index()
    {
        return $this->finder('ectools_mdl_trade_paybill',array(
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            'title' => app::get('systrade')->_('支付单列表'),
            'use_buildin_delete'=>false,
            'use_buildin_export'=>true,
            //'base_filter' => array('status|notin'=>['succ','progress','failed','error','invalid','timeout']),
        ));
    }
}

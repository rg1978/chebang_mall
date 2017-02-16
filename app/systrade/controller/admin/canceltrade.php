<?php

class systrade_ctl_admin_canceltrade extends desktop_controller {

    public function index()
    {
        return $this->finder('systrade_mdl_trade_cancel',array(
            'use_buildin_filter'=>true,
            'title' => app::get('systrade')->_('取消订单列表'),
            'use_buildin_delete'=>false,
        ));
    }
}


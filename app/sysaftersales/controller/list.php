<?php

class sysaftersales_ctl_list extends desktop_controller {

    public $workground = 'sysaftersales.workground.aftersale';

    public function index()
    {
        return $this->finder(
            'sysaftersales_mdl_aftersales',
            array(
                'title'=>app::get('sysaftersales')->_('售后申请列表'),
                'use_buildin_delete'=>false,
                'use_buildin_filter'=>true,
            )
        );
    }

    public function _views()
    {
        $sub_menu = array(
            1=>array(
                'label'=>app::get('systrade')->_('退货退款'),
                'optional'=>false,
                'filter'=>array(
                    'aftersales_type'=>'REFUND_GOODS'
                )
            ),
            2=>array(
                'label'=>app::get('systrade')->_('换货'),
                'optional'=>false,
                'filter'=>array(
                    'aftersales_type'=>'EXCHANGING_GOODS'
                )
            ),
        );
        return $sub_menu;
    }
}

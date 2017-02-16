<?php
/**
 * @brief 平台操作日志
 */
class system_ctl_admin_apilog extends desktop_controller {

    /**
     * @brief  平台操作日志
     *
     * @return
     */
    public function index()
    {
        return $this->finder('system_mdl_apilog',array(
            'use_buildin_delete' => true,
            'title' => app::get('system')->_('API日志'),
            'actions'=>array(),
        ));
    }

    public function _views()
    {
        $sub_menu = array(
            0=>array('label'=>app::get('systrade')->_('全部'),'optional'=>false),
            1=>array('label'=>app::get('systrade')->_('运行中'),'optional'=>false,'filter'=>array('status'=>'running')),
            2=>array('label'=>app::get('systrade')->_('成功'),'optional'=>false,'filter'=>array('status'=>'success')),
            4=>array('label'=>app::get('systrade')->_('失败'),'optional'=>false,'filter'=>array('status'=>'fail')),
        );

        return $sub_menu;
    }

    public function edit()
    {
        $apilogId = input::get('apilog_id');
        $pagedata = kernel::single('system_apilog')->get($apilogId, '*');
        $pagedata['params'] = unserialize($pagedata['params']);
        if( $pagedata['result'] )
        {
            $pagedata['result'] = unserialize($pagedata['result']);
        }
        return $this->page('system/admin/apiloginfo.html', $pagedata);
    }

}



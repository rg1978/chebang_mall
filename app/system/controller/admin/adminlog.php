<?php
/**
 * @brief 平台操作日志
 */
class system_ctl_admin_adminlog extends desktop_controller {

    /**
     * @brief  平台操作日志
     *
     * @return
     */
    public function index()
    {
        if($this->has_permission('export')){
            $use_buildin_export = true;
        }
        return $this->finder('system_mdl_adminlog',array(
            'use_buildin_delete' => false,
            'use_buildin_export' => $use_buildin_export,
            'title' => app::get('system')->_('操作日志'),
            'actions'=>array(),
        ));
    }

}



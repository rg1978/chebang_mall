<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author afei, bryant
 */


class system_ctl_admin_queueFailed extends desktop_controller {

    var $workground = 'system.workground.setting';

    public function index()
    {
        $params = array (
            'title' => app::get('system')->_('失败队列管理'),
        );

        return $this->finder('system_mdl_queue_failed', $params);
    }

    public function edit()
    {
        $id = input::get('id');
        $pagedata = app::get('system')->model('queue_failed')->getRow('id,data,queue_name,reason,create_time', ['id'=>$id]);
        return $this->page('system/admin/queue/failed.html', $pagedata);
    }

    public function exec()
    {
        $this->begin('?app=system&ctl=admin_queueFailed&act=index');
        $id = input::get('id');

        $shell = new base_shell_loader;
        try
        {
            ob_start();
            $shell->exec_command('system:queue failed_exec '. $id);
            ob_clean();
        }
        catch( Exception $e)
        {
            $this->end(false, '执行失败');
        }

        $this->adminlog("执行失败队列成功[{$id}]", 1);
        $this->end(true, '执行成功');
    }
}


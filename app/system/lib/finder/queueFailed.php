<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class system_finder_queueFailed {

    var $column_control = '查看';
    var $column_control_order = 'HEAD';

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function column_control(&$colList, $list)
    {
        foreach($list as $k => $row)
        {
            $colList[$k] = '<a href="?app=system&ctl=admin_queueFailed&act=edit&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&id=' . $row['id'] . '" target="dialog::{title:\'' . app::get('system')->_('队列数据详情') . '\', width:680, height:500}">' . app::get('system')->_('查看队列数据');
        }
    }

    public $column_exec = '操作';
    public function column_exec(&$colList, $list)
    {
        foreach($list as $k => $row)
        {
            $html = view::make('system/admin/queue/failedConsumer.html',['id'=>$row['id']]);
            $colList[$k] = $html;
        }
    }
}


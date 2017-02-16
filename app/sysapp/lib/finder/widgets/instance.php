<?php

class sysapp_finder_widgets_instance {

    public $column_edit = '操作';
    public $column_edit_order = 1;
    public $column_edit_width = 60;
    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $urltmpl = '?app=sysapp&ctl=admin_tmpl&act=edit&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['widgets_id'];
            $target = 'dialog::{title:\''.app::get('sysapp')->_('编辑模块').'\', width:400, height:200}';
            $title = app::get('sysapp')->_('编辑');
            $html = '<a href="' . $urltmpl . '" target="' . $target . '">' . $title . '</a>';

            $url = '?app=sysapp&ctl=admin_widgets&act=edit_widgets&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['widgets_id'];
            $target = 'dialog::{title:\''.app::get('sysapp')->_('编辑挂件').'\', width:900, height:500}';
            $title = app::get('sysapp')->_('配置挂件');
            $html .= '  |  <a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            $colList[$k] = $html;
        }
    }
 
}

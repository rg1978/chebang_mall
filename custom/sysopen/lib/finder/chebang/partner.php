<?php
/**
 * Created by PhpStorm.
 * User: zhoumin
 * Date: 2015/10/14
 * Time: 16:44
 */
class sysopen_finder_chebang_partner {
    public $column_edit = '编辑';
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list){
        foreach($list as $k=>$row)
        {
            $url = '?app=sysopen&ctl=admin_chebang_partner&act=create&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['cp_id'];
            $target = 'dialog::  {title:\''.app::get('sysopen')->_('编辑').'\', width:500, height:350}';
            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . app::get('sysopen')->_('编辑') . '</a>';
        }
    }
}
<?php
class sysshop_finder_subdomain {

    public $column_edit = '操作';
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list){
        foreach($list as $k=>$row)
        {
            $url = '?app=sysshop&ctl=admin_subdomain&act=edit&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&shop_id='.$row['shop_id'];
            $target = 'dialog::  {title:\''.app::get('sysshop')->_('编辑二级域名').'\', width:400, height:250}';

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . app::get('sysshop')->_('编辑') . '</a>';
        }
    }

}


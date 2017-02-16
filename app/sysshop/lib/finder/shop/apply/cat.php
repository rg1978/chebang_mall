<?php
class sysshop_finder_shop_apply_cat{
    public $column_edit = '操作';
    public $column_edit_order = 4;
    public $column_edit_width = 200;


    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            if($row['check_status'] == "pending")
            {
                $url = '?app=sysshop&ctl=admin_applycat&act=goExamine&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&apply_id='.$row['apply_id'];
                $target = 'dialog::  {title:\''.app::get('sysshop')->_('审核商家类目权限').'\', width:800, height:400}';
                $title = app::get('sysshop')->_('审核');
                $return = ' <a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
                $colList[$k] = $return;
            }
        }

    }

}

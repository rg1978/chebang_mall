<?php

class syspromotion_finder_hongbao {

    public $column_edit = "编辑";
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_edit(&$colList,$list)
    {
        $nowTime = time();
        foreach($list as $k=>$row)
        {
            $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'editHongbao','ctl'=>'admin_hongbao','finder_id'=>$_GET['_finder']['finder_id'],'hongbao_id'=>$row['hongbao_id']]);
            if( $row['status'] == 'pending' )
            {
                $title = '编辑';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('编辑红包信息').'\', width:800, height:600}';
            }
            elseif( $row['status'] == 'active' )
            {
                $title = '终止红包领取';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('终止红包领取').'\', width:800, height:600}';
            }
            elseif( $row['status'] == 'stop' && $row['get_end_time'] > time() )
            {
                $title = '重启红包领取';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('重启红包领取').'\', width:800, height:600}';
            }
            else
            {
                $title = '查看';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('查看红包信息').'\', width:800, height:600}';
            }

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }
    }
 }

<?php

class sysrate_finder_traderate {

    public $column_edit = '操作';
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_edit(&$colList, $list)
    {
        foreach( $list as $k=>$row )
        {
            $url = '?app=sysrate&ctl=traderate&act=showRateView&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['rate_id'];
            $target = 'dialog::{title:\''.app::get('sysrate')->_('查看评价详情').'\', width:800, height:400}';
            $title = '查看';

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }
    }
}


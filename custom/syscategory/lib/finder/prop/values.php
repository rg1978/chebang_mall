<?php
class syscategory_finder_prop_values {

    public $column_edit = '操作';
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list){
        foreach($list as $k=>$row)
        {
			// 2016-01-17 zhoumin  新增审核操作
			$result = "";
			if ($row['status'] != 'successful')
			{
				$url = '?app=syscategory&ctl=admin_props&act=doExamineValues&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['prop_value_id'];
				$target = 'dialog::  {title:\''.app::get('syscategory')->_('审核').'\', width:400, height:400}';
				$result = '<a href="' . $url . '" target="' . $target . '">' . app::get('syscategory')->_('审核') . '</a>';
			}	        
	        $colList[$k] = $result;   
        }
    }
}


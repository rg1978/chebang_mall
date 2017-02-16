<?php
	class sysopen_finder_chebang_appurl {
		public $column_edit = '操作';
    	public $column_edit_order = 2;
    	public $column_edit_width = 200;

	    /**
	     * @brief 编辑链接
	     *
	     * @param $row
	     *
	     * @return page
	     */

	    public function column_edit(&$colList, $list) {
	        foreach($list as $k=>$row)
	        {
	            $colList[$k] = $this->_column_edit($row);
	        }
	    }

	    public function _column_edit($row) {
			$url = '?app=sysopen&ctl=admin_chebang_appurl&act=doEdit&finder_id=' . $_GET['_finder']['finder_id'] . '&url_id=' . $row['url_id'];
            $target = 'dialog::{title:\''.app::get('sysopen')->_('编辑').'\',width:400, height:250}';
            $title = app::get('sysopen')->_('编辑');
            return '<a href="'.$url.'" target="'.$target.'">' . $title . '</a>';
    	}
	}
?>
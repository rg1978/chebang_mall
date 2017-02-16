<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class system_finder_apilog {

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
            $colList[$k] = '<a href="?app=system&ctl=admin_apilog&act=edit&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&apilog_id=' . $row['apilog_id'] . '" target="dialog::{title:\'' . app::get('system')->_('API日志信息') . '\', width:680, height:500}">' . app::get('system')->_('查看详情');
        }
    }
}

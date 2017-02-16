<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_finder_tag {

    public function __construct($app)
    {
        $this->app = $app;

        $this->column_edit = app::get('sysuser')->_('操作');
        $this->column_show = app::get('sysuser')->_('效果');
    }

    /**
     * @brief 操作列显示的信息(two)
     *
     * @param $row
     *
     * @return
     */
    public $column_edit;
    public $column_edit_width=220;
    public $column_edit_order = 10;
    public function column_edit(&$colList, $list)
    {

        foreach($list as $k=>$row)
        {
            $colList[$k] = "<a href='?app=sysuser&ctl=admin_tag&act=edit&tag_id={$row['tag_id']}&_finder[finder_id]={$_GET['_finder']['finder_id']}&finder_id={$_GET['_finder']['finder_id']}' target='dialog::{title:\"".app::get('syscategory')->_('添加标签')."\",width:500,height:350}' >编辑</a>";
        }
    }

    /**
     * @brief 操作列显示的信息(two)
     *
     * @param $row
     *
     * @return
     */
    public $column_show;
    public $column_show_width=220;
    public $column_show_order = 10;
    public function column_show(&$colList, $list)
    {

        foreach($list as $k=>$row)
        {
            $colList[$k] = '<span style=" background: '.$row['tag_color'].'; padding: 2px 4px; margin-right: 3px; color: #4E6A81; border-radius: 4px;">' . $row['tag_name']. '</span>';
        }
    }


}


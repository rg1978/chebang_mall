<?php
class sysopen_finder_keys{
    public $column_edit = '操作';
    public $column_edit_keys = 1;
    public $column_edit_width = 60;

    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            if($row['contact_type'] == 'applyforopen')
            {
                $url = '?app=sysopen&ctl=admin_shop&act=doApply&finder_id=' . $_GET['_finder']['finder_id'] . '&shop_id=' . $row['shop_id'];
                $target = 'dialog::{title:\''.app::get('systrade')->_('审核').'\',width:300, height:200}';
                $title = app::get('systrade')->_('平台审核');
                $colList[$k] = '<a href="'.$url.'" target="'.$target.'">' . $title . '</a>';
            }
            elseif($row['contact_type'] == 'notallowopen')
            {
                $url = '?app=sysopen&ctl=admin_shop&act=open&finder_id=' . $_GET['_finder']['finder_id'] . '&shop_id=' . $row['shop_id'];
                $title = app::get('systrade')->_('重启商家权限');
                $colList[$k] = '<a href="'.$url.'">' . $title . '</a>';
            }
            else
            {
                $url = '?app=sysopen&ctl=admin_shop&act=doSuspend&finder_id=' . $_GET['_finder']['finder_id'] . '&shop_id=' . $row['shop_id'];
                $title = app::get('systrade')->_('禁用商家权限');
                $colList[$k] = '<a href="'.$url.'">' . $title . '</a>';
            }
        }
    }

    public $column_shopname = '店铺名称';
    public $column_shopname_width = 100;
    public function column_shopname(&$colList, $list)
    {
        $shopIds = array_column($list, 'shop_id');
        if( !$shopIds ) return $colList;

        $shopData = app::get('sysopen')->rpcCall('shop.get.list',['shop_id'=>implode(',',$shopIds),'fields'=>'shop_name,shop_id']);
        $shopData = array_bind_key($shopData,'shop_id');
        foreach($list as $k=>$row)
        {
            $colList[$k] = $shopData[$row['shop_id']]['shop_name'];
        }
    }
}


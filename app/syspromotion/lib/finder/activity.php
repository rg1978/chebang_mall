<?php
class syspromotion_finder_activity{
    public $column_edit = "编辑";
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_edit(&$colList,$list)
    {
        $nowTime = time();
        foreach($list as $k=>$row)
        {
            $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'editActivity','ctl'=>'admin_activity','finder_id'=>$_GET['_finder']['finder_id'],'id'=>$row['activity_id']]);
            $title = '编辑';
            $target = '_blank';
            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }
    }

    public $column_href = "预览";
    public $column_href_order = 2;
    public function column_href(&$colList,$list)
    {
        foreach($list as $k=>$row)
        {
            $url = url::action('topc_ctl_activity@activity_item_list', ['id'=>$row['activity_id']]);
            $url2 = url::action('topwap_ctl_activity@detail', ['id'=>$row['activity_id']]);
            $target = '';
            $str = '<a href="' . $url . '" target="_blank"> pc预览 </a>';
            $str2 = '<a href="' . $url2 . '" target="_blank"> wap预览 </a>';
            $colList[$k] =$str." | ".$str2;
        }
    }

    public $detail_basic = '基本信息';
    public function detail_basic($Id)
    {
        $objActivity = kernel::single('syspromotion_activity');
        $activity = $objActivity->getInfo("*",array('activity_id'=>$Id));
        $pagedata = $activity;
        return view::make('syspromotion/activity/detail.html',$pagedata)->render();
    }
}

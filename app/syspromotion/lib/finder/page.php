<?php

/**
 * page.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_finder_page {
    
    public $column_edit = '操作';
    public $column_edit_order = 1;
    public $column_edit_width = 60;
    
    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
                $url = '?app=syspromotion&ctl=admin_page&act=add&finder_id='.$_GET['_finder']['finder_id'].'&page_id='.$row['page_id'];
                $target = 'dialog::{title:\''.app::get('syspromotion')->_('编辑专题').'\', width:800, height:400}';
                $title = app::get('syspromotion')->_('编辑');
                $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
    
        }
    }
    
    public $detail_basic = '促销专题详情';
    public function detail_basic($id)
    {
        $page = kernel::single('syspromotion_page')->getInfo($id);
        $pagedata['page'] = $page;
        $url = '';
        if($page['used_platform'] == 'pc')
        {
            $url = url::action("topc_ctl_promotion@ProjectPage",array('page_id'=>$id));
        }
        else
        {
            $url = url::action("topwap_ctl_promotion@ProjectPage",array('page_id'=>$id));
        }
        
        $pagedata['qrcode'] = getQrcodeUri($url, 80, 10);
        return view::make('syspromotion/admin/page/detail.html',$pagedata)->render();
    }
}
 
<?php

/**
 * page.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_ctl_admin_page extends desktop_controller {

    public $workground = 'site.wrokground.theme';
    public $platforms = array('pc'=>'电脑端','wap'=>'移动端');
    public function index()
    {
        $target = 'dialog::{title:\''.app::get('syspromotion')->_('添加专题').'\', width:800, height:400}';
        $actions = [
                [
                        'label' => app::get('syspromotion')->_('添加专题'), 
                        'href' => '?app=syspromotion&ctl=admin_page&act=add',
                        'target'=>$target,
                ] 
        ];
        
        return $this->finder('syspromotion_mdl_page', [
                'use_buildin_set_tag' => false, 
                'use_buildin_tagedit' => true, 
                'use_buildin_filter' => false, 
                'use_buildin_refresh' => false, 
                'use_buildin_delete' => true, 
                'title' => app::get('syspromotion')->_('促销专题列表'), 
                'actions' => $actions 
        ]);
    }
    
    // 添加专题页面
    public function add()
    {
        $pageId = input::get('page_id', 0);
        $page['display_time'] = time();
        if(intval($pageId) > 0)
        {
            $page = kernel::single('syspromotion_page')->getInfo($pageId);
        }
        
        $pagedata['platform_options'] = $this->platforms;
        $pagedata ['page'] = $page;
        return $this->page('syspromotion/admin/page/editor.html', $pagedata);
    }
    
    // 保存数据
    public function save()
    {
        $post = input::get();
        // 处理时间参数
        $H = $post['_DTIME_']['H']['page[display_time'];
        $M = $post['_DTIME_']['M']['page[display_time'];
        $postdata = $post['page'];
        $postdata['display_time'] = $postdata['display_time'].' '.$H.':'.$M;
        $this->begin("?app=syspromotion&ctl=admin_page&act=index");
        try {
            kernel::single('syspromotion_page')->saveData($postdata);
            $this->adminlog("保存促销专题：{$postdata['title']}", 1);
        } catch (Exception $e) {
            
            $this->adminlog("保存促销专题：{$postdata['title']}", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        
        $this->end(true);
    }

    public function _views()
    {

        $subMenu = array(
                0 => array(
                        'label' => app::get('syspromotion')->_('全部'), 
                        'optional' => false 
                ), 
                1 => array(
                        'label' => app::get('syspromotion')->_('pc'), 
                        'optional' => false, 
                        'filter' => array(
                                'used_platform' => 'pc' 
                        ) 
                ), 
                2 => array(
                        'label' => app::get('syspromotion')->_('wap'), 
                        'optional' => false, 
                        'filter' => array(
                                'used_platform' => 'wap' 
                        ) 
                ) 
        );
        
        return $subMenu;
    }

}
 
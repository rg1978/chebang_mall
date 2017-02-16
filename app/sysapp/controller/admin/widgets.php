<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_ctl_admin_widgets extends desktop_controller {

    /*
     * workground
     * @var string
     */
    var $workground = 'site.wrokground.theme';

    public function __construct(&$app)
    {
        $this->tmpls = kernel::single('sysapp_module_config')->tmpls;// 页面类型
        $this->widgets = kernel::single('sysapp_module_config')->widgets;// 挂件类型
        $this->linktype = kernel::single('sysapp_module_config')->linktype;// 对应app端页面类型，用于app端判断怎么跳转页面

        parent::__construct($app);
    }
    public function edit_widgets($widgetsId)
    {
        $objMdlWidgetsInstance = app::get('sysapp')->model('widgets_instance');
        $winfo = $objMdlWidgetsInstance->getRow('*', array('widgets_id'=>$widgetsId));
        $pagedata['setting'] = $winfo['params'] ;
        $pagedata['_PAGE_'] = 'sysapp/widgets/'.$winfo['widget'].'/_config.html';
        $pagedata['widgets_id'] = $widgetsId;
        $pagedata['widget'] = $winfo['widget'];

        return view::make('sysapp/main_widgets.html', $pagedata);
    }

    public function save_widgets()
    {
        $postdata = input::get();
        $widgetsId = input::get('widgets_id');
        $widgetFunc = input::get('widget');
        unset($postdata['app']);
        unset($postdata['ctl']);
        unset($postdata['act']);
        unset($postdata['widgets_id']);
        unset($postdata['widget']);
        $this->begin("?app=sysapp&ctl=admin_tmpl&act=index");
        try
        {
            //挂件配置保存
            $objMdlWidgetsInstance = app::get('sysapp')->model('widgets_instance');
            if(method_exists($this, $widgetFunc))
            {
                $postdata = call_user_func([$this, $widgetFunc], $postdata);
            }
            $flag = $objMdlWidgetsInstance->update( ['params'=>$postdata], ['widgets_id'=>$widgetsId] );
            if(!$flag)
            {
                throw new \LogicException(app::get('sysapp')->_('配置挂件失败'));
            }
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false, $msg);
        }

        $this->adminlog("配置挂件[widgets_id:{$widgetsId}]", 1);
        $this->end('true');
    }

    public function output($pagedata)
    {
        if( $pagedata['_PAGE_'] ){
            $pagedata['_PAGE_'] = 'topc/member/'.$pagedata['_PAGE_'];
        }else{
            $pagedata['_PAGE_'] = 'topc/member/'.$this->action_view;
        }
        return $this->page('topc/member/main.html', $pagedata);
    }

    protected function slider($params)
    {
        $newParams['pic'] =[];
        foreach($params['pic'] as $v)
        {
            if( !$v['link'] && !$v['linktarget'] && !$v['linkinfo'] ) continue;

            $newParams['pic'][] = [
                'link' => $v['link'],
                'linktarget' => $v['linktarget'],
                'linkinfo' => $v['linkinfo'],
                'linktype' => $v['linktype'],
            ];
        }
        return $newParams;
    }

    protected function floor($params)
    {
        if($params['styletag']=='one')
        {
            unset($params['pic']['2']);
            unset($params['pic']['3']);
        }
        return $params;
    }

    protected function category_nav($params)
    {
        $newParams['pic'] =[];
        foreach($params['pic'] as $v)
        {
            if( !$v['categoryname'] && !$v['linkinfo'] && !$v['link'] && !$v['linktarget'] ) continue;

            $newParams['pic'][] = [
                'categoryname' => $v['categoryname'],
                'linkinfo' => $v['linkinfo'],
                'cat_id' => $v['cat_id'],
                'image' => $v['image'],
            ];
        }

        return $newParams;
    }

}//End Class

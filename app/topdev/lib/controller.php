<?php

class topdev_controller extends base_routing_controller {

    public function __construct()
    {
        if (!config::get('app.debug'))
        {
            return kernel::abort(404);
        }

        pamAccount::setAuthType('desktop');
        if( ! pamAccount::getAccountId() )
        {
            $url = url::route('shopadmin');
            echo "<script>location ='$url'</script>";exit;
        }

        if( ! kernel::single('desktop_user')->is_super() )
        {
            return kernel::abort(404);
        }

        $this->loginName = pamAccount::getLoginName();
    }

    public function page($view, $pagedata = array())
    {
        $pagedata['topdevSysData']['loginName'] = $this->loginName;

        $pagedata['topdevSysData']['activeMenu'] = $this->activeMenu;
        $pagedata['runtimePath'] = $this->runtimePath;
        $menu = $this->getMenu();
        $pagedata['sysMenu'] = $menu;
        $pagedata['output']['view'] = $view;

        if( !$this->tmplName )
        {
            $this->tmplName = 'topdev/tmpl/index.html';
        }

        return view::make($this->tmplName, $pagedata);
    }

    public function getMenu()
    {
        $devMenu = kernel::single('topdev_menu');
        $devMenu->group(array('group_name' => '桌面'), function() use ($devMenu) {
            $devMenu->add('桌面', 'topdev_ctl_index@index', null, 'fa fa-dashboard');
        });

        $devMenu->group(array('group_name' => 'API管理'), function() use ($devMenu) {

            $devMenu->group(array('group_name' => '系统API','icon'=>'fa fa-th-list'), function() use ($devMenu) {
                $list = kernel::single('topdev_apis')->getApiGroupList();
                foreach( $list as $row )
                {
                    $devMenu->add($row['name'], array('topdev_ctl_apis@group',['group'=>$row['name'],'apitype'=>'apis']) ,$row['count']);
                }
            });

            $devMenu->group(array('group_name'=>'APP聚合API', 'icon'=>'fa fa-th'),  function() use ($devMenu) {
                $list = kernel::single('topdev_apis')->getTopApiGroupList();
                foreach( $list as $row )
                {
                    $devMenu->add($row['name'], array('topdev_ctl_apis@group',['group'=>$row['name'], 'apitype'=>'topapi']) ,$row['count']);
                }
            });
        });

        return $devMenu->getMenu();
    }
}


<?php
	class sysopen_ctl_admin_chebang_appurl extends desktop_controller {
		public function index() {
			return $this->finder('sysopen_mdl_chebang_appurl', array(
            'use_buildin_delete' => true,
            'title' => app::get('syssopen')->_('App链接列表'),
            'actions' => array(
                array(
                    'label'=>app::get('syssopen')->_('新建'),
                    'href'=>'?app=sysopen&ctl=admin_chebang_appurl&act=addUrl',
                    'target'=>'dialog::{title:\''.app::get('sysopen')->_('新建').'\',  width:400,height:250}',
                ),
            )));
    	}

    	public function addUrl() {
	    	$this->contentHeaderTitle = '新建链接';
        	return view::make('sysopen/admin/chebang/addUrl.html');
    	}

    	public function saveUrl() {
	    	$postdata = utils::_filter_input(input::get('chebang'));
	    	$mdlUrl = app::get('sysopen')->model('chebang_appurl');
			try
	        {
		        $postdata['create_time'] = time();
		        $mdlUrl->save($postdata);
	            $this->adminlog("新建App链接[{$postdata['url_name']}]", 1);
	        }
	        catch(Exception $e)
	        {
	            $this->adminlog("新建App链接[{$postdata['url_name']}]", 0);
	            $msg = $e->getMessage();
	            return $this->splash('error',null,$msg);
	        }
	        return $this->splash('success',null,"链接新建成功");
    	}

    	public function doEdit() {
	    	$urlId = $_GET['url_id'];
	    	$mdlUrl = app::get('sysopen')->model('chebang_appurl');
			$urlData = $mdlUrl->getRow('*', array('url_id' => $urlId));
			$postdata['chebang'] = $urlData;
			//var_dump($postdata);
			return view::make('sysopen/admin/chebang/editUrl.html', $postdata);
     	}

     	public function updateUrl() {
	     	$postdata = utils::_filter_input(input::get('chebang'));
	     	$mdlUrl = app::get('sysopen')->model('chebang_appurl');
	     	//var_dump($postdata);
	     	//exit();
	     	try
	        {
		        $mdlUrl->save($postdata);
	            $this->adminlog("更新App链接[{$postdata['url_name']}]", 1);
	        }
	        catch(Exception $e)
	        {
	            $this->adminlog("更新App链接[{$postdata['url_name']}]", 0);
	            $msg = $e->getMessage();
	            return $this->splash('error',null,$msg);
	        }
	        return $this->splash('success',null,"链接更新成功");
     	}
	}	
?>
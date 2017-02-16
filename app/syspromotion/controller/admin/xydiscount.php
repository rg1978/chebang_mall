<?php
class syspromotion_ctl_admin_xydiscount extends desktop_controller{

    public function index()
    {
        return $this->finder('syspromotion_mdl_xydiscount',array(
            'title' => app::get('syspromotion')->_('X件Y折列表'),
            'use_buildin_delete' => false,
            'use_view_tab'=>true,
            'actions' => array(

            ),
        ));
    }

    public function approve()
    {
        $data = input::get();

        if( !trim($data['reason']) && $data['xydiscount_status'] == 'refuse' )
        {
            return $this->splash('error',null,'请填写驳回原因',true);
        }

        $apiData = array(
            'xydiscount_id' => (int)$data['xydiscount_id'],
        	'status' => $data['xydiscount_status'],
        	'shop_id' => (int)$data['shop_id'],
        	'reason' => trim($data['reason']),
        );

        $logInfo=array(
            'time' => time(),
            'approve_status' => $data['xydiscount_status'],
            'reason' => $data['reason']
        );

        try{
        	$result = app::get('syspromotion')->rpcCall('promotion.xydiscount.approve',$apiData);
        	if ($result) {
                redis::scene('syspromotion')->rpush('xydiscount_id_'.$data['xydiscount_id'],serialize($logInfo));
                $this->adminlog("X件Y折审核状态：[{$data['xydiscount_status']}]，X件Y折ID：{$data['xydiscount_id']}", 1);
        		return $this->splash('success',null,'操作成功',true);
        	}else{
                $this->adminlog("X件Y折审核状态：[{$data['xydiscount_status']}]，X件Y折ID：{$data['xydiscount_id']}", 1);
        		return $this->splash('error',null,'操作失败',true);
        	}
        } catch(\LogicException $e){
        	return $this->splash('success',null,$e->getMessage(),true);
        }
    }

    public function refuse()
    {
        $pagedata = input::get();
        return view::make('syspromotion/activity/xydiscount/refuse.html', $pagedata);
    }

    public function _views()
    {
        if(app::get('sysconf')->getConf('shop.promotion.examine')){
            $sub_menu = array(
                1=>array(
                    'label'=>app::get('syspromotion')->_('全部'),
                    'optional'=>false,
                    'filter'=>array()
                ),
                2=>array(
                    'label'=>app::get('syspromotion')->_('未审核'),
                    'optional'=>false,
                    'filter'=>array(
                        'xydiscount_status'=>'pending'
                    )
                ),
                3=>array(
                    'label'=>app::get('syspromotion')->_('审核通过'),
                    'optional'=>false,
                    'filter'=>array(
                        'xydiscount_status'=>'agree'
                    )
                ),
                4=>array(
                    'label'=>app::get('syspromotion')->_('审核失败'),
                    'optional'=>false,
                    'filter'=>array(
                        'xydiscount_status'=>'refuse'
                    )
                ),
            );
        }
        return $sub_menu;
    }



}
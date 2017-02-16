<?php
class syspromotion_ctl_admin_fullminus extends desktop_controller{

    public function index()
    {
        return $this->finder('syspromotion_mdl_fullminus',array(
            'title' => app::get('syspromotion')->_('满减列表'),
            'use_buildin_delete' => false,
            'use_view_tab'=>true,
            'actions' => array(

            ),
        ));
    }

    public function approve()
    {
        $data = input::get();
        if( !trim($data['reason']) && $data['fullminus_status'] == 'refuse' )
        {
            return $this->splash('error',null,'请填写驳回原因',true);
        }

        $apiData = array(
            'fullminus_id' => (int)$data['fullminus_id'],
            'status' => $data['fullminus_status'],
            'shop_id' => (int)$data['shop_id'],
            'reason' => trim($data['reason']),
        );

        $logInfo=array(
            'time' => time(),
            'approve_status' => $data['fullminus_status'],
            'reason' => $data['reason']
        );

        try{
            $result = app::get('syspromotion')->rpcCall('promotion.fullminus.approve',$apiData);
            if ($result) {
                redis::scene('syspromotion')->rpush('fullminus_id_'.$data['fullminus_id'],serialize($logInfo));
                $this->adminlog("满减审核状态：[{$data['fullminus_status']}]，满减ID：{$data['fullminus_id']}", 1);
	           return $this->splash('success',null,'操作成功',true);
            }else{
                $this->adminlog("满减审核状态：[{$data['fullminus_status']}]，满减ID：{$data['fullminus_id']}", 0);
                return $this->splash('error',null,'操作失败',true);
            }
        } catch(\LogicException $e){
            return $this->splash('success',null,$e->getMessage(),true);
        }
    }

    public function refuse()
    {
        $pagedata = input::get();
        return view::make('syspromotion/activity/fullminus/refuse.html', $pagedata);
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
                        'fullminus_status'=>'pending'
                    )
                ),
                3=>array(
                    'label'=>app::get('syspromotion')->_('审核通过'),
                    'optional'=>false,
                    'filter'=>array(
                        'fullminus_status'=>'agree'
                    )
                ),
                4=>array(
                    'label'=>app::get('syspromotion')->_('审核失败'),
                    'optional'=>false,
                    'filter'=>array(
                        'fullminus_status'=>'refuse'
                    )
                ),
            );
        }
        return $sub_menu;
    }
}

<?php

class syspromotion_ctl_admin_gift extends desktop_controller{
	public function index()
    {
        return $this->finder('syspromotion_mdl_gift',array(
            'title' => app::get('syspromotion')->_('赠品活动列表'),
            'use_buildin_delete' => false,
            'actions' => array(

            ),
        ));
    }

    public function approve()
    {
        $data = input::get();

        if( !trim($data['reason']) && $data['gift_status'] == 'refuse' )
        {
            return $this->splash('error',null,'请填写驳回原因',true);
        }

        $apiData = array(
            'gift_id' => (int)$data['gift_id'],
            'status' => $data['gift_status'],
            'shop_id' => (int)$data['shop_id'],
            'reason' => trim($data['reason']),
        );

        $logInfo=array(
            'time' => time(),
            'approve_status' => $data['gift_status'],
            'reason' => $data['reason']
        );

        try{
            $result = app::get('syspromotion')->rpcCall('promotion.gift.approve',$apiData);
            if ($result) {
                redis::scene('syspromotion')->rpush('gift_id_'.$data['gift_id'],serialize($logInfo));
                $this->adminlog("赠品审核状态：[{$data['gift_status']}]，赠品促销ID：{$data['gift_id']}", 1);
	            return $this->splash('success',null,'操作成功',true);
            }else{
                $this->adminlog("赠品审核状态：[{$data['gift_status']}]，赠品促销ID：{$data['gift_id']}", 0);
                return $this->splash('error',null,'操作失败',true);
            }
        } catch(\LogicException $e){
            return $this->splash('success',null,$e->getMessage(),true);
        }
    }

    public function refuse()
    {
        $pagedata = input::get();
        return view::make('syspromotion/activity/gift/refuse.html', $pagedata);
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
                        'gift_status'=>'pending'
                    )
                ),
                3=>array(
                    'label'=>app::get('syspromotion')->_('审核通过'),
                    'optional'=>false,
                    'filter'=>array(
                        'gift_status'=>'agree'
                    )
                ),
                4=>array(
                    'label'=>app::get('syspromotion')->_('审核失败'),
                    'optional'=>false,
                    'filter'=>array(
                        'gift_status'=>'refuse'
                    )
                ),
            );
        }
        return $sub_menu;
    }
}

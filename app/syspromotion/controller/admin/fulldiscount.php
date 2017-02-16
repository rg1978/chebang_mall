<?php
class syspromotion_ctl_admin_fulldiscount extends desktop_controller{

    public function index()
    {
        return $this->finder('syspromotion_mdl_fulldiscount',array(
            'title' => app::get('syspromotion')->_('满折列表'),
            'use_buildin_delete' => false,
            'use_view_tab'=>true,
            'actions' => array(

            ),
        ));
    }

    public function approve()
    {
        $data = input::get();

        if( !trim($data['reason']) && $data['fulldiscount_status'] == 'refuse' )
        {
            return $this->splash('error',null,'请填写驳回原因',true);
        }

        $apiData = array(
            'fulldiscount_id' => (int)$data['fulldiscount_id'],
            'status' => $data['fulldiscount_status'],
            'shop_id' => (int)$data['shop_id'],
            'reason' => trim($data['reason']),
        );

        $logInfo=array(
            'time' => time(),
            'approve_status' => $data['fulldiscount_status'],
            'reason' => $data['reason']
        );

        try{
            $result = app::get('syspromotion')->rpcCall('promotion.fulldiscount.approve',$apiData);
            if ($result) {
                redis::scene('syspromotion')->rpush('fulldiscount_id_'.$data['fulldiscount_id'],serialize($logInfo));
                $this->adminlog("满折审核状态：[{$data['fulldiscount_status']}]，满折促销ID：{$data['fulldiscount_id']}", 1);
                return $this->splash('success',null,'操作成功',true);
            }else{
                $this->adminlog("满折审核状态：[{$data['fulldiscount_status']}]，满折促销ID：{$data['fulldiscount_id']}", 0);
                return $this->splash('error',null,'操作失败',true);
            }
        } catch(\LogicException $e){
            return $this->splash('success',null,$e->getMessage(),true);
        }
    }

    public function refuse()
    {
        $pagedata = input::get();
        return view::make('syspromotion/activity/fulldiscount/refuse.html', $pagedata);
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
                        'fulldiscount_status'=>'pending'
                    )
                ),
                3=>array(
                    'label'=>app::get('syspromotion')->_('审核通过'),
                    'optional'=>false,
                    'filter'=>array(
                        'fulldiscount_status'=>'agree'
                    )
                ),
                4=>array(
                    'label'=>app::get('syspromotion')->_('审核失败'),
                    'optional'=>false,
                    'filter'=>array(
                        'fulldiscount_status'=>'refuse'
                    )
                ),
            );
        }
        return $sub_menu;
    }

}

<?php

class syspromotion_ctl_admin_hongbao extends desktop_controller {

    public function index()
    {
        return $this->finder('syspromotion_mdl_hongbao',array(
            'actions'=>array(
                array(
                    'label'=>app::get('syspromotion')->_('新增红包'),
                    'target'=>'dialog::{ title:\''.app::get('sysuser')->_('新增红包').'\', width:800, height:600}',
                    'href'=>'?app=syspromotion&ctl=admin_hongbao&act=editHongbao',
                ),
            ),
            'title' => app::get('syspromotion')->_('红包列表'),
            'use_buildin_delete' => false,
        ));
    }

    public function editHongbao()
    {
        $pagedata = array();

        if( input::get('hongbao_id') )
        {
            $apiParams['hongbao_id'] = input::get('hongbao_id');
            $apiParams['fields'] = '*';
            $pagedata = app::get('syspromotion')->rpcCall('promotion.hongbao.get', $apiParams);
            if( $pagedata['status'] != 'pending' )
            {
                if( $pagedata['status'] == 'active' )
                {
                    $pagedata['btn_title'] = '终止红包领取';
                    $pagedata['update_status'] = 'stop';
                }
                elseif( $pagedata['status'] == 'stop' && $pagedata['get_end_time'] > time() )
                {
                    $pagedata['btn_title'] = '重启红包领取';
                    $pagedata['update_status'] = 'active';
                }
                else
                {
                    $pagedata['btn_title'] = '关闭';
                }
                return $this->page('syspromotion/hongbao/info.html', $pagedata);
            }
        }

        return $this->page('syspromotion/hongbao/edit.html', $pagedata);
    }

    public function stopIssuedHongbao()
    {
        $this->begin();
        if( ! input::get('status') )
        {
            $this->end(true);
        }

        try
        {
            app::get('syspromotion')->rpcCall('promotion.hongbao.updateStatus',['hongbao_id'=>input::get('hongbao_id'),'status'=>input::get('status')]);
        }
        catch( Exception $e )
        {
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }

        $this->end(true);
    }

    public function saveHongbao()
    {
        $data = input::get();
        if( $data['hongbao_id'] )
        {
            $apiParams['hongbao_id'] = $data['hongbao_id'];
            if( $data['status'] )
            {
                $apiParams['status'] = $data['status'];
            }
        }
        $apiParams['name'] = $data['name'];
        $apiParams['user_total_money'] = $data['user_total_money'];
        $apiParams['user_total_num'] = $data['user_total_num'];
        $apiParams['used_platform'] = $data['used_platform'];
        $apiParams['hongbao_type'] = $data['hongbao_type'];

        $useStartTime = $data['use_start_time'];
        //处理时间参数
        $H = $data['_DTIME_']['H'];
        $M = $data['_DTIME_']['M'];
        foreach($H as $key=>$val)
        {
            $apiParams[$key] = strtotime($data[$key]." ".$val.":".$M[$key]);
        }

        if( empty($data['money']) )
        {
            return $this->splash('error',null,'请填写红包规则');
        }

        foreach( $data['money'] as $k=>$v )
        {
            if( $v )
            {
                $hongbaoList[$k]['money'] = $v;
                $hongbaoList[$k]['num'] = $data['num'][$k];
            }
        }

        $apiParams['hongbao_list'] = json_encode($hongbaoList);

        $this->begin("?app=syspromotion&ctl=admin_hongbao&act=index");
        try
        {
            app::get('syspromotion')->rpcCall('promotion.hongbao.create', $apiParams);
            $this->adminlog("添加红包{$apiParams['name']}", 1);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true);
    }
}



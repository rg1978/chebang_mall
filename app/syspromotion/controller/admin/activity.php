<?php
class syspromotion_ctl_admin_activity extends desktop_controller{

    public function index()
    {
        return $this->finder('syspromotion_mdl_activity', array(
            'title' => app::get('syspromotion')->_('活动管理'),
            'use_buildin_delete' => false,
            'actions' => array(
                array(
                    'label' => app::get('syspromotion')->_('添加活动'),
                    'target' => '_blank',
                    'href' => url::route('shopadmin', ['app'=>'syspromotion','act'=>'add','ctl'=>'admin_activity']),
                ),
                array(
                    'label' => app::get('syspromotion')->_('开售提醒设置'),
                    'target' => 'dialog::{title:\''.app::get('syspromotion')->_('开售提醒设置').'\',width:500,height:350}',
                    'href' => url::route('shopadmin', ['app'=>'syspromotion','act'=>'setRemind','ctl'=>'admin_activity']),
                ),
                array(
                    'label'=>app::get('sysitem')->_('删除'),
                    'icon' => 'download.gif',
                    'submit' => url::route('shopadmin', ['app'=>'syspromotion','act'=>'doDelete','ctl'=>'admin_activity']),
                    'confirm' => app::get('sysitem')->_('确定要删除选中的活动？'),
                ),
            ),
        )
    );
    }

    public function setRemind()
    {
        $postdata = input::get('remind');
        if(!$postdata)
        {
            $pagedata = app::get('syspromotion')->getConf('activity.mobile.remind.number');
            return view::make('syspromotion/activity/set_remind.html', $pagedata);
        }
        $this->begin("?app=syspromotion&ctl=admin_activity&act=index");
        if(!$postdata['mobile_num'] || intval($postdata['mobile_num']) <=0)
        {
            $this->end(false,'请输入大于零的整数');
        }
        $result = app::get('syspromotion')->setConf('activity.mobile.remind.number',$postdata);
        $this->end($result);
    }

    public function add()
    {
        $this->contentHeaderTitle = '添加活动';
        $pagedata = $this->__publicData();
        $pagedata['status'] = "1";
        return $this->singlepage('syspromotion/activity/add.html',$pagedata);
    }

    /**
     * @brief 未开始报名时编辑活动
     *
     * @return
     */
    public function editActivity()
    {
        $id = input::get('id');
        $pagedata = $this->__getActivity($id);
        return $this->singlepage('syspromotion/activity/add.html',$pagedata);
    }

    public function save()
    {
        $post = input::get();
        $ruledata = $post['ruledata'];

        //处理时间参数
        $H = $post['_DTIME_']['H'];
        $M = $post['_DTIME_']['M'];
        foreach($H as $key=>$val)
        {
            $ruledata[$key] = strtotime($post[$key]." ".$val.":".$M[$key]);
        }

        if($ruledata['activity_id'])
        {
            //编辑时验证post值
            $result = $this->__checkEditPost($ruledata,$msg);
        }
        else
        {
            //添加时验证post值
            $result = $this->__checkAddPost($ruledata,$msg);
        }
        if(!$result)
        {
            return $this->splash('error',null,$msg);
        }
        $this->begin("?app=syspromotion&ctl=admin_activity&act=index");
        try
        {
            kernel::single('syspromotion_activity')->saveActivity($ruledata);
            $this->adminlog("添加活动{$ruledata['activity_name']}", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("添加活动{$ruledata['activity_name']}", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true);

    }

    private function __publicData()
    {
        // 获取店铺类型
        $shopType = app::get('syspromotion')->rpcCall('shop.type.get');
        $shopType['self'] = array(
            'shop_type' => 'self',
            'name' => '运营商自营',
        );
        $pagedata['shoptype'] = $shopType;

        //获取类目
        $cat = app::get('syspromotion')->rpcCall('category.cat.get.info',array('level' =>1));
        $pagedata['cat'] = $cat;

        return $pagedata;
    }

    public function doSetMainpush()
    {
        $post = input::get('activity');
        $post['activity_name'] = $post['activity_name'][$post['activity_id']];
        $this->begin();
        try
        {
            kernel::single('syspromotion_activity')->setMainpush($post);
            $this->adminlog("设置主推活动{$post['activity_name']}", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("设置主推活动{$post['activity_name']}", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true);
    }


    public function doDelete()
    {
        $ids = input::get('activity_id');
        $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'index','ctl'=>'admin_activity']);
        $this->begin($url);
        try
        {
            kernel::single('syspromotion_activity')->deleteActivity($ids);
            $this->adminlog("删除活动{$ids}", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("删除活动{$ids}", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true);
    }

    private function __getActivity($id)
    {
        $nowtime = time();
        $pagedata = $this->__publicData();
        $objMdlActivity = app::get('syspromotion')->model('activity');
        $ruleInfo = $objMdlActivity->getRow('*',array('activity_id'=>$id));
        if($ruleInfo['apply_begin_time'] > $nowtime)
        {
            $pagedata['status'] = "1";
        }
        elseif($ruleInfo['apply_begin_time'] < $nowtime && $nowtime < $ruleInfo['release_time'])
        {
            $pagedata['status'] = "2";
        }
        elseif($ruleInfo['release_time'] < $nowtime && $nowtime < $ruleInfo['end_time'])
        {
            $pagedata['status'] = "3";
        }
        else
        {
            $pagedata['status'] = "4";
        }
        $shopType = explode(',',$ruleInfo['shoptype']);
        foreach($shopType as $v)
        {
            if($pagedata['shoptype'][$v])
            {
                $pagedata['shoptype'][$v]['checked'] = true;
            }
        }

        $catData = $ruleInfo['limit_cat'];
        foreach($catData as $id)
        {
            if($pagedata['cat'][$id])
            {
                $pagedata['cat'][$id]['checked'] = true;
            }
        }

       $pagedata['ruleInfo'] = $ruleInfo;
        return $pagedata;
    }

    private function __checkAddPost($ruledata,&$msg=null)
    {
        $validator = validator::make(
            array(
                '0'=>$ruledata['buy_limit'] ,
                '1'=>$ruledata['discount_min'],
                '2'=>$ruledata['discount_max'],
                '3'=>date('Y-m-d H:i:s',$ruledata['apply_begin_time']),
                '4'=>date('Y-m-d H:i:s',$ruledata['apply_end_time']),
                '5'=>date('Y-m-d H:i:s',$ruledata['release_time']),
                '6'=>date('Y-m-d H:i:s',$ruledata['start_time']),
                '7'=>date('Y-m-d H:i:s',$ruledata['end_time']),
                '8'=>$ruledata['shoptype'],
                '9'=>$ruledata['limit_cat'],
                '10'=>$ruledata['activity_name'],
                '11'=>$ruledata['activity_tag'],
                '12'=>$ruledata['slide_images'],
            ),
            array(
                '0'=>'min:0',
                '1'=>'numeric|min:0.01|max:99.99',
                '2'=>'numeric|max:99.99|min:'.$ruledata['discount_min'],
                '3'=>'after:'.date('Y-m-d H:i:s',time()),
                '4'=>'after:'.date('Y-m-d H:i:s',$ruledata['apply_begin_time']),
                '5'=>'after:'.date('Y-m-d H:i:s',$ruledata['apply_end_time']),
                '6'=>'after:'.date('Y-m-d H:i:s',$ruledata['release_time']),
                '7'=>'after:'.date('Y-m-d H:i:s',$ruledata['start_time']),
                '8'=>'required',
                '9'=>'required',
                '10'=>'required|max:20',
                '11'=>'required|max:10',
                '12'=>'required',
            ),
            array(
                '0'=>'用户限购数量要大于0!',
                '1'=>'折扣必须是数字|折扣范围必须大于0|折扣范围必须小于100',
                '2'=>'折扣必须是数字|折扣范围必须小于100|折扣范围必须由小到大！',
                '3'=>'活动报名的开始时间必须大于当前时间！',
                '4'=>'活动报名结束时间必须大于报名的开始时间！',
                '5'=>'发布时间必须大于报名结束时间！',
                '6'=>'活动生效时间必须大于活动发布时间！',
                '7'=>'活动生效结束时间必须大于活动开始时间！',
                '8'=>'至少选择一种店铺类型！',
                '9'=>'至少选择一种平台商品类目！',
                '10'=>'|活动名称长度必须小于20',
                '11'=>'|活动标签长度必须小于10',
                '12'=>'活动宣传图必填',
            )
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                $msg = $error[0];
                return false;
            }
        }
        if($ruledata['remind_enabled'] )
        {
            if(!$ruledata['remind_time'])
            {
                $msg = app::get('syspromotion')->_('请填写提醒时间');
               return false;
            }

            if(!isset($ruledata['remind_way']) || !in_array($ruledata['remind_way'],['email','mobile']))
            {
                $msg = app::get('syspromotion')->_('请至少选择一项提醒方式');
                return false;
            }
        }
        return true;
    }

    private function __checkEditPost($ruledata,&$msg)
    {
        $objMdlActivity = app::get('syspromotion')->model('activity');
        $data = $objMdlActivity->getRow('apply_end_time,apply_begin_time,start_time',['activity_id'=>$ruledata['activity_id']]);

        if(!$ruledata['status'] || $ruledata['status'] == 1)
        {
            if($ruledata['apply_begin_time'] < $data['apply_begin_time'])
            {
                $msg = "活动报名的开始时间必须大于编辑前的开始时间！";
                return false;
            }
            $validator = validator::make(
                array(
                    '0'=>$ruledata['buy_limit'] ,
                    '1'=>$ruledata['discount_min'],
                    '2'=>$ruledata['discount_max'],
                    '4'=>date('Y-m-d H:i:s',$ruledata['apply_end_time']),
                    '5'=>date('Y-m-d H:i:s',$ruledata['release_time']),
                    '6'=>date('Y-m-d H:i:s',$ruledata['start_time']),
                    '7'=>date('Y-m-d H:i:s',$ruledata['end_time']),
                    '8'=>$ruledata['shoptype'],
                    '9'=>$ruledata['limit_cat'],
                    '10'=>$ruledata['activity_name'],
                    '11'=>$ruledata['activity_tag'],
                    '12'=>$ruledata['slide_images'],
                ),
                array(
                    '0'=>'min:0',
                    '1'=>'numeric|min:0.01|max:99.99',
                    '2'=>'numeric|max:99.99|min:'.$ruledata['discount_min'],
                    '4'=>'after:'.date('Y-m-d H:i:s',$ruledata['apply_begin_time']),
                    '5'=>'after:'.date('Y-m-d H:i:s',$ruledata['apply_end_time']),
                    '6'=>'after:'.date('Y-m-d H:i:s',$ruledata['release_time']),
                    '7'=>'after:'.date('Y-m-d H:i:s',$ruledata['start_time']),
                    '8'=>'required',
                    '9'=>'required',
                    '10'=>'required|max:20',
                    '11'=>'required|max:10',
                    '12'=>'required',
                ),
                array(
                    '0'=>'用户限购数量要大于0!',
                    '1'=>'折扣必须是数字|折扣范围必须大于0|折扣范围必须小于100',
                    '2'=>'折扣必须是数字|折扣范围必须小于100|折扣范围必须由小到大！',
                    '4'=>'活动报名结束时间必须大于报名的开始时间！',
                    '5'=>'发布时间必须大于报名结束时间！',
                    '6'=>'活动生效时间必须大于活动发布时间！',
                    '7'=>'活动生效结束时间必须大于活动开始时间！',
                    '8'=>'至少选择一种店铺类型！',
                    '9'=>'至少选择一种平台商品类目！',
                    '10'=>'|活动名称长度必须小于20',
                    '11'=>'|活动标签长度必须小于10',
                    '12'=>'活动宣传图必填',
                )
            );
        }
        elseif($ruledata['status'] == 2)
        {
            $validator = validator::make(
                array(
                    '4'=>date('Y-m-d H:i:s',$ruledata['apply_end_time']),
                    '5'=>date('Y-m-d H:i:s',$ruledata['release_time']),
                    '6'=>date('Y-m-d H:i:s',$ruledata['start_time']),
                    '7'=>date('Y-m-d H:i:s',$ruledata['end_time']),
                    '12'=>$ruledata['slide_images'],
                ),
                array(
                    '4'=>'after:'.date('Y-m-d H:i:s',$data['apply_begin_time']),
                    '5'=>'after:'.date('Y-m-d H:i:s',$ruledata['apply_end_time']),
                    '6'=>'after:'.date('Y-m-d H:i:s',$ruledata['release_time']),
                    '7'=>'after:'.date('Y-m-d H:i:s',$ruledata['start_time']),
                    '12'=>'required',
                ),
                array(
                    '4'=>'活动报名结束时间必须大于报名的开始时间！',
                    '5'=>'发布时间必须大于报名结束时间！',
                    '6'=>'活动生效时间必须大于活动发布时间！',
                    '7'=>'活动生效结束时间必须大于活动开始时间！',
                    '12'=>'活动宣传图必填',
                )
            );
        }
        elseif($ruledata['status'] == 3)
        {
            $validator = validator::make(
                array(
                    '7'=>date('Y-m-d H:i:s',$ruledata['end_time']),
                ),
                array(
                    '7'=>'after:'.date('Y-m-d H:i:s',$data['start_time']),
                ),
                array(
                    '7'=>'活动生效结束时间必须大于活动开始时间！',
                )
            );
        }

        if ($validator && $validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                $msg = $error[0];
                return false;
            }
        }

        if($ruledata['remind_enabled'] )
        {
            if(!$ruledata['remind_time'])
            {
                $msg = app::get('syspromotion')->_('请填写提醒时间');
                return false;
            }

            if(!isset($ruledata['remind_way']) || !in_array($ruledata['remind_way'],['email','mobile']))
            {
                $msg = app::get('syspromotion')->_('请至少选择一项提醒方式');
                return false;
            }
        }
        return true;
    }
}



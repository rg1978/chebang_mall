<?php
class topshop_ctl_promotion_fullminus extends topshop_controller {

    public function list_fullminus()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('满减管理');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 10;
        $params = array(
            'page_no' => $filter['pages'],
            'page_size' => intval($pageSize),
            'fields' =>'*',
            'shop_id'=> $this->shopId,
        );
        $fullminusListData = app::get('topshop')->rpcCall('promotion.fullminus.list', $params,'seller');
        $count = $fullminusListData['total'];
        $pagedata['fullminusList'] = $fullminusListData['data'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_promotion_fullminus@list_fullminus', $filter),
            'current'=>$current,
            'use_app'=>'topshop',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $gradeList = app::get('topshop')->rpcCall('user.grade.list');
        // 组织会员等级的key,value的数组，方便取会员等级名称
        $gradeKeyValue = array_bind_key($gradeList, 'grade_id');

        // 增加列表中会员等级名称字段
        foreach($pagedata['fullminusList'] as &$v)
        {
            $valid_grade = explode(',', $v['valid_grade']);

            $checkedGradeName = array();
            foreach($valid_grade as $gradeId)
            {
                $checkedGradeName[] = $gradeKeyValue[$gradeId]['grade_name'];
            }
            $v['valid_grade_name'] = implode(',', $checkedGradeName);
            $v['condition_value'] = $this->condition($v['condition_value']);

        }

        $pagedata['now'] = time();
        $pagedata['total'] = $count;
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.promotion.examine');

        return $this->page('topshop/promotion/fullminus/index.html', $pagedata);
    }

    public function edit_fullminus()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('添加/编辑满减促销');

        $apiData['fullminus_id'] = input::get('fullminus_id');
        $apiData['fullminus_itemList'] = true;
        $pagedata['valid_time'] = date('Y/m/d H:i', time()+60) . '-' . date('Y/m/d H:i', time()+120); //默认时间
        if($apiData['fullminus_id'])
        {
            $pagedata = app::get('topshop')->rpcCall('promotion.fullminus.get', $apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i', $pagedata['start_time']) . '-' . date('Y/m/d H:i', $pagedata['end_time']);
            if($pagedata['shop_id']!= $this->shopId)
            {
                return $this->splash('error','','您没有权限编辑此满减促销',true);
            }
            $notItems = array_column($pagedata['itemsList'], 'item_id');
            $pagedata['notEndItem'] =  json_encode($notItems,true);
        }
        $valid_grade = explode(',', $pagedata['valid_grade']);
        $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');
        foreach($pagedata['gradeList'] as &$v)
        {
            if( in_array($v['grade_id'], $valid_grade) )
            {
                $v['is_checked'] = true;
            }
        }

        $pagedata['condition_value'] = $this->condition($pagedata['condition_value']);
        $pagedata['ac'] = input::get('ac', '');

        return $this->page('topshop/promotion/fullminus/edit.html', $pagedata);
    }
    //查看满减 by wujie
    public function show_fullminus()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('查看满减促销');
        $apiData['fullminus_id'] = input::get('fullminus_id');
        $apiData['fullminus_itemList'] = true;
        if($apiData['fullminus_id'])
        {
            $pagedata = app::get('topshop')->rpcCall('promotion.fullminus.get',$apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i',$pagedata['start_time']) . '  ~  '.date('Y/m/d H:i',$pagedata['end_time']);
            if($pagedata['shop_id']!=$this->shopId)
            {
                return $this->splash('error','','您没有权限查看此满减促销',true);
            }
            $notItems = array_column($pagedata['itemList'],'item_id');
            $pagedata['notEndItem'] = json_encode($notItems,true);
        }
        $valid_grade = explode(',', $pagedata['valid_grade']);
        $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');

        $gradeIds = array_column($pagedata['gradeList'], 'grade_id');
        if(!array_diff($gradeIds, $valid_grade))
        {
            $gradeStr = '所有会员';
        }else
        {
            foreach ($pagedata['gradeList'] as $mem)
            {
                if(in_array($mem['grade_id'], $valid_grade))
                {
                    $gradeStr .= $mem['grade_name'].'，';
                }
            }
            $gradeStr = rtrim($gradeStr, '，');
        }
        $pagedata['greade_str'] = $gradeStr;
        $pagedata['condition_value'] = $this->condition($pagedata['condition_value']);
        $pagedata['ac'] = input::get('ac', '');
        return $this->page('topshop/promotion/fullminus/show.html',$pagedata);
    }

    public function condition($condition)
    {
        $condList = explode(',',$condition);
        foreach ($condList as $key => $value)
        {
            $condList[$key] = explode('|',$value);
        }
        return $condList;
    }

    public function save_fullminus()
    {
        $params = input::get();

        $apiData['fullminus_id'] = $params['fullminus_id'];
        $apiData['fullminus_name'] = $params['fullminus_name'];
        $apiData['canjoin_repeat'] = intval($params['canjoin_repeat']);
        $apiData['join_limit'] = intval($params['join_limit']);
        $apiData['used_platform'] = intval($params['used_platform']);
        $apiData['free_postage'] = intval($params['free_postage']);
        //优惠规则描述
        if($params['role_desc'])
        {
            //限制长度
            $len = mb_strlen($params['role_desc'],'utf-8');
            if($len >50)
            {
                return $this->splash('error','','规则描述不能超过50个字',true);
            }

            $apiData['fullminus_desc'] = htmlspecialchars(strip_tags($params['role_desc']), ENT_QUOTES, 'UTF-8');
        }

        if( !$params['fullminus_name'] )
        {
            return $this->splash('error','','满减促销名称不能为空!',true);
        }
        if( !is_array($params['full']) && count($params['full']) && !is_array($params['minus']) && count($params['minus']) )
        {
            return $this->splash('error','','满减条件至少添加一个!',true);
        }
        if(count($params['item_id'])>=1000)
        {
            return $this->splash('error','','最多添加1000个商品!',true);
        }
        $full = $params['full'];
        $minus = $params['minus'];
        $joinfullminus = array();
        foreach($full as $k=>$v)
        {
            $joinfullminus[] = $v.'|'.$minus[$k];
        }
        $apiData['condition_value'] = implode(',', $joinfullminus);
        $apiData['shop_id'] = $this->shopId;
        $timeArray = explode('-', $params['valid_time']);
        $apiData['start_time']  = strtotime($timeArray[0]);
        $apiData['end_time'] = strtotime($timeArray[1]);
        $apiData['valid_grade'] = implode(',', $params['grade']);
        $apiData['fullminus_rel_itemids'] = implode(',', array_unique($params['item_id'])); // 满减关联的商品id,格式 商品id  '23,99,103',以逗号分割
        try
        {
            if($params['fullminus_id'])
            {
                // 修改满减促销
                $result = app::get('topshop')->rpcCall('promotion.fullminus.update', $apiData);
            }
            else
            {
                // 新添满减促销
                $result = app::get('topshop')->rpcCall('promotion.fullminus.add', $apiData);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            if($params['fullminus_id'])
            {
                $url = url::action('topshop_ctl_promotion_fullminus@edit_fullminus', array('fullminus_id'=>$params['fullminus_id']));
            }
            else{
                $url = url::action('topshop_ctl_promotion_fullminus@list_fullminus');
            }
            return $this->splash('error',$url,$msg,true);
        }
        $this->sellerlog('添加/修改满减促销。满减促销名称是 '.$apiData['fullminus_name']);
        $url = url::action('topshop_ctl_promotion_fullminus@list_fullminus');
        $msg = app::get('topshop')->_('保存满减促销成功');
        return $this->splash('success',$url,$msg,true);
    }

    //提交审核
    public function submit_approve(){
        $apiData = input::get();
        try{

            $fullminusInfo = app::get('topshop')->rpcCall('promotion.fullminus.get',$apiData);
            if($fullminusInfo['end_time'] <= time()){
                throw new \LogicException('您的活动已过期，无法提交审核!');
            }

            $result = app::get('topshop')->rpcCall('promotion.fullminus.approve',$apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('更新满减促销。满减促销ID是 '.$apiData['fullminus_id']);
        $url = url::action('topshop_ctl_promotion_fullminus@list_fullminus');
        $msg = app::get('topshop')->_('提交审核成功');
        return $this->splash('success', $url, $msg, true);
    }

    public function delete_fullminus()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['fullminus_id'] = input::get('fullminus_id');
        $url = url::action('topshop_ctl_promotion_fullminus@list_fullminus');
        try
        {
            app::get('topshop')->rpcCall('promotion.fullminus.delete', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('删除满减促销。满减促销ID是 '.$apiData['fullminus_id']);
        $msg = app::get('topshop')->_('删除满减促销成功');
        return $this->splash('success', $url, $msg, true);
    }

    public function cancel_fullminus()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['fullminus_id'] = input::get('fullminus_id');
        $url = url::action('topshop_ctl_promotion_fullminus@list_fullminus');
        try
        {
            app::get('topshop')->rpcCall('promotion.fullminus.cancel', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('取消满减促销。满减促销ID是 '.$apiData['fullminus_id']);
        $msg = app::get('topshop')->_('取消满减促销成功');
        return $this->splash('success', $url, $msg, true);
    }

    //根据商家id和3级分类id获取商家所经营的所有品牌
    public function getBrandList()
    {
        $shopId = $this->shopId;
        $catId = input::get('catId');
        $params = array(
            'shop_id'=>$shopId,
            'cat_id'=>$catId,
            'fields'=>'brand_id,brand_name,brand_url'
        );
        $brands = app::get('topshop')->rpcCall('category.get.cat.rel.brand',$params);
        return response::json($brands);
    }
}


<?php
class topshop_ctl_promotion_package extends topshop_controller {

    public function list_package()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('组合促销管理');
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
        $packageListData = app::get('topshop')->rpcCall('promotion.package.list', $params,'seller');
        $count = $packageListData['total'];
        $pagedata['packageList'] = $packageListData['data'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_promotion_package@list_package', $filter),
            'current'=>$current,
            'use_app'=>'topshop',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $gradeList = app::get('topshop')->rpcCall('user.grade.list');
        // 组织会员等级的key,value的数组，方便取会员等级名称
        $gradeKeyValue = array_bind_key($gradeList, 'grade_id');

        // 增加列表中会员等级名称字段
        foreach($pagedata['packageList'] as &$v)
        {
            $valid_grade = explode(',', $v['valid_grade']);

            $checkedGradeName = array();
            foreach($valid_grade as $gradeId)
            {
                $checkedGradeName[] = $gradeKeyValue[$gradeId]['grade_name'];
            }
            $v['valid_grade_name'] = implode(',', $checkedGradeName);
        }

        $pagedata['now'] = time();
        $pagedata['total'] = $count;
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.promotion.examine');

        return $this->page('topshop/promotion/package/index.html', $pagedata);
    }

    public function edit_package()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('添加/编辑组合促销促销');

        $apiData['package_id'] = input::get('package_id');
        $apiData['package_itemList'] = true;
        $pagedata['valid_time'] = date('Y/m/d H:i', time()+60) . '-' . date('Y/m/d H:i', time()+120); //默认时间
        if($apiData['package_id'])
        {
            $pagedata = app::get('topshop')->rpcCall('promotion.package.get', $apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i', $pagedata['start_time']) . '-' . date('Y/m/d H:i', $pagedata['end_time']);
            if($pagedata['shop_id']!= $this->shopId)
            {
                return $this->splash('error','','您没有权限编辑此组合促销促销',true);
            }
            $packageItems = array_column($pagedata['itemsList'], 'item_id');
            $pagedata['notEndItem'] =  json_encode($packageItems,true);

            $pagedata['selectorExtendsData'] = json_encode($pagedata['itemsList'], true);

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
        return $this->page('topshop/promotion/package/edit.html', $pagedata);
    }

    //
    public function show_package(){
        $this->contentHeaderTitle = app::get('topshop')->_('查看组合促销');
        $apiData['package_id'] = input::get('package_id');
        $apiData['package_itemList'] = true;
        if($apiData['package_id'])
        {
            $pagedata = app::get('topshop')->rpcCall('promotion.package.get',$apiData);
            $pagedata['valid_time'] = date('Y/m/d H:i',$pagedata['start_time']).' ~ '.date('Y/m/d H:i',$pagedata['end_time']);
            if($pagedata['shop_id'] != $this->shopId)
            {
                return $this->splash('error','','您没有权限查看此组合促销',true);
            }
            $packageItems = array_column($pagedata['itemsList'], 'item_id');
            $pagedata['notEndItem'] = json_encode($packageItems,true);
            $pagedata['selectorExtendsData'] = json_encode($pagedata['itemsList'],true);

            $valid_grade = explode(',', $pagedata['valid_grade']);
            $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');
            $gradeId = array_column($pagedata['gradeList'],'grade_id');
            if( !array_diff($gradeId, $valid_grade))
            {
                $gradeStr = '所有会员';
            }
            else
            {
                foreach ($pagedata['gradeList'] as $member) {
                    if(in_array($member['grade_id'],$valid_grade))
                    {
                        $gradeStr .= $member['grade_name'].',';
                    }
                }
                $gradeStr = rtrim($gradeStr);
            }
            $pagedata['grade_str'] = $gradeStr;
        }
        $pagedata['ac'] = input::get('ac');
        return $this->page('topshop/promotion/package/show.html',$pagedata);
    }

    public function save_package()
    {

        $params = input::get();

        $len = mb_strlen($params['package_name'],'UTF-8');
        if($len >10)
        {
                return $this->splash('error','','促销名称不能超过10个字',true);
        }
        if(is_null($params['used_platform']))
        {
             return $this->splash('error','','使用平台不能为空!',true);
        }

        foreach ($params['itemList'] as $packageItem) {
            if(!$packageItem['package_price']){
                return $this->splash('error','','组合商品价格不能为空！',true);
            }
        }
        $apiData['package_id'] = $params['package_id'];
        $apiData['package_name'] = $params['package_name'];
        $apiData['used_platform'] = intval($params['used_platform']);
        $apiData['free_postage'] = intval($params['free_postage']);
        $apiData['package_item_list'] = $params['itemList'];

        //gen package_total_price
        $package_total_price = 0;
        foreach($params['itemList'] as $package_item)
        {
            $package_total_price += $package_item['package_price'];
        }

        $apiData['package_total_price'] = $package_total_price;

        if( !$params['package_name'] )
        {
            return $this->splash('error','','组合促销名称不能为空!',true);
        }

        $apiData['shop_id'] = $this->shopId;
        $timeArray = explode('-', $params['valid_time']);
        $apiData['start_time']  = strtotime($timeArray[0]);
        $apiData['end_time'] = strtotime($timeArray[1]);
        $apiData['valid_grade'] = implode(',', $params['grade']);

        $apiData['package_rel_itemids'] = implode(',', array_unique($params['item_id'])); // 组合促销关联的商品id,格式 商品id  '23,99,103',以逗号分割
        try
        {
            if($params['package_id'])
            {
                // 修改组合促销促销
                $result = app::get('topshop')->rpcCall('promotion.package.update', $apiData);
            }
            else
            {
                // 新添组合促销促销
                $result = app::get('topshop')->rpcCall('promotion.package.add', $apiData);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            if($params['package_id'])
            {
                $url = url::action('topshop_ctl_promotion_package@edit_package', array('package_id'=>$params['package_id']));
            }
            else{
                $url = url::action('topshop_ctl_promotion_package@list_package');
            }
            return $this->splash('error',$url,$msg,true);
        }
        $this->sellerlog('添加/修改组合促销。组合促销名称是 '.$apiData['package_name']);
        $url = url::action('topshop_ctl_promotion_package@list_package');
        $msg = app::get('topshop')->_('保存组合促销成功');
        return $this->splash('success',$url,$msg,true);
    }

    //提交审核
    public function submit_approve(){
        $apiData = input::get();
        $packageInfo = app::get('topshop')->rpcCall('promotion.package.get',$apiData);
        try{
            if($packageInfo['end_time'] <= time()){
                throw new \LogicException('您的活动已过期，无法提交审核!');
            }
            $result = app::get('topshop')->rpcCall('promotion.package.approve',$apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('更新组合促销促销。组合促销ID是 '.$apiData['package_id']);
        $url = url::action('topshop_ctl_promotion_package@list_package');
        $msg = app::get('topshop')->_('提交审核成功');
        return $this->splash('success', $url, $msg, true);
    }

    public function delete_package()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['package_id'] = input::get('package_id');
        $url = url::action('topshop_ctl_promotion_package@list_package');
        try
        {
            app::get('topshop')->rpcCall('promotion.package.delete', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('删除组合促销。组合促销ID是 '.$apiData['package_id']);
        $msg = app::get('topshop')->_('删除组合促销促销成功');
        return $this->splash('success', $url, $msg, true);
    }

    public function cancel_package()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['package_id'] = input::get('package_id');
        $url = url::action('topshop_ctl_promotion_package@list_package');
        try
        {
            app::get('topshop')->rpcCall('promotion.package.cancel', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $this->sellerlog('取消组合促销。组合促销ID是 '.$apiData['package_id']);
        $msg = app::get('topshop')->_('取消组合促销成功');
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



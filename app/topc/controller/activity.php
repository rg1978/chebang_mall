<?php
class topc_ctl_activity extends topc_controller{

    public $limit = 40;

    // 所有活动首页
    public function index()
    {
        $this->setLayoutFlag('activity_index');
        $post = input::get();
        $params = array(
            'release_time' => "sthan",
            'end_time' => "bthan",
            'order_by' => 'mainpush desc',
            'fields' => 'activity_name,activity_id,mainpush,slide_images,release_time,end_time,start_time,discount_max,discount_min',
        );
        $activitys = app::get('topc')->rpcCall('promotion.activity.list',$params);
        //echo "<pre>";print_r($activitys);exit;
        if(!$data = $activitys['data'])
        {
            return $this->page("topc/promotion/empty.html", $pagedata);
        }
        $now = time();
        $nostartCount = 0;
        $startCount = 0;
        foreach($data as $key=>$val)
        {
            if($now >= $val['release_time'] && $now < $val['start_time'] )
            {
                $pagedata['activity_list_nostart'][] = $val;
                $nostartCount += 1;
            }
            elseif($now >= $val['start_time'] && $now < $val['end_time'] )
            {
                $pagedata['activity_list_start'][] = $val;
                $startCount += 1;
            }
        }
        $pagedata['nostart_count'] = $nostartCount ;
        $pagedata['start_count'] = $startCount ;
        $pagedata['now_time'] = time();

        return $this->page("topc/promotion/activity_index.html",$pagedata);
    }

    public function activity_item_list()
    {
        $post = utils::_filter_input(input::get());
        $post['id'] = $post['id'];

        $pagedata = $this->__getPagedata($post);

        if($activity = $pagedata['activity'])
        {
            if($activity['release_time'] > time())
            {
                return kernel::abort(404);
            }
        }
        //echo "<pre>";print_r($pagedata);exit;
        try
        {
            $cativityData = app::get('topc')->rpcCall('promotion.activity.info',array('activity_id'=>$post['id'],'fields'=>'limit_cat'));
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            //echo '<pre>';print_r($msg);exit();
            return $this->splash('error',null,$msg);
        }
        
        // 分页重写
        $page = $post['pages'] ? $post['pages'] : 1;
        if( $pagedata['total'] > 0 ) $totalPage = ceil($pagedata['total']/$this->limit);
        $post['pages'] = time();
        $pagedata['pagers'] = array(
                'link'=>url::action('topc_ctl_activity@activity_item_list',$post),
                'current'=>$page,
                'total'=>$totalPage,
                'token'=>time(),
        );
        
        $pagedata['catlist']  = $cativityData['limit_cat'];
        $pagedata['now_time'] = time();
        return $this->page("topc/promotion/activity_item_list.html",$pagedata);
    }

    // 具体某个活动的商品列表
    public function itemlist()
    {
        $post = input::get();
        $pagedata = $this->__getPagedata($post);
        return view::make('topc/promotion/list.html',$pagedata);
    }

    // 活动商品的详细页
    public function detail()
    {
        if( userAuth::check() )
        {
            $pagedata['nologin'] = 1;
        }
        $this->setLayoutFlag('product');
        $post = input::get();
        $params['fields'] = "*";
        $params['activity_id'] = $post['a'];
        $params['item_id'] = $post['g'];
        $groupItem = app::get('topc')->rpcCall('promotion.activity.item.info',$params);
        $activity = $groupItem['activity_info'];
        if($activity['release_time'] > time())
        {
            redirect::action('topc_ctl_item@index',array('item_id'=>$params['item_id']))->send();exit;
        }
        unset($groupItem['activity_info']);
        $pagedata['group_item'] = $groupItem;
        $pagedata['activity'] = $activity;
        $pagedata['item'] = app::get('topc')->rpcCall('item.get',array('item_id'=>$params['item_id'],'fields'=>'item_id,item_count.sold_quantity,item_count.item_id,item_desc.pc_desc'));
        $pagedata['shop'] = app::get('topc')->rpcCall('shop.get',array('shop_id'=>$pagedata['group_item']['shop_id'],'fields'=>'shop_name,shop_id'));
        // 获取店铺子域名
        $pagedata['shop']['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$pagedata['shop']['shop_id']))['subdomain'];

        $pagedata['shopDsrData'] = $this->__getShopDsr($pagedata['shop']['shop_id']);
        $pagedata['now_time'] = time();
        return $this->page("topc/promotion/activity_detail.html",$pagedata);
    }

    // 获取店铺的dsr信息
    private function __getShopDsr($shopId)
    {
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = true;
        $dsrData = app::get('topc')->rpcCall('rate.dsr.get', $params);
        if( !$dsrData )
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',5.0);
            $countDsr['attitude_dsr'] = sprintf('%.1f',5.0);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',5.0);
        }
        else
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',$dsrData['tally_dsr']);
            $countDsr['attitude_dsr'] = sprintf('%.1f',$dsrData['attitude_dsr']);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',$dsrData['delivery_speed_dsr']);
        }
        $shopDsrData['countDsr'] = $countDsr;
        $shopDsrData['catDsrDiff'] = $dsrData['catDsrDiff'];
        return $shopDsrData;
    }

    private function __getPagedata($post)
    {
        $pagedata['filter'] = $post;
        $page = $post['pages'] ? $post['pages'] : 1;
        $pageSize = $this->limit;
        $orderBy = $post['orderBy'];
        $params = array(
            'status' => 'agree',
            'page_no' => intval($page),
            'page_size' => $pageSize,
            'order_by' => $orderBy,
            'fields' => 'title,item_default_image,price,item_id,activity_id,sales_count,activity_price,cat_id',
        );
        if($post['id'])
        {
            $params['activity_id'] = $post['id'];
        }

        if($post['cat_id'])
        {
            if(is_array($post['cat_id']))
            {
                $params['cat_id'] = implode(',',$post['cat_id']);
            }
            else
            {
                $params['cat_id'] = $post['cat_id'];
            }
        }
        $item = app::get('topc')->rpcCall('promotion.activity.item.list',$params);
        $pagedata['group_item'] = $item['list'];
        $pagedata['activity'] = app::get('topc')->rpcCall('promotion.activity.info',array('activity_id' => $params['activity_id'],'fields'=>'activity_id,activity_name,slide_images,activity_tag,start_time,end_time,release_time,discount_max,discount_min,remind_enabled'));;
        $pagedata['total'] = $item['count'];
        if( $pagedata['total'] > 0 ) $totalPage = ceil($pagedata['total']/$this->limit);
        $post['pages'] = time();
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_activity@index',$post),
            'current'=>$page,
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['now_time'] = time();
        return $pagedata;
    }

    private function __getCatLv1Lv3($activityItem,$id)
    {
        $activityData = app::get('topc')->rpcCall('promotion.activity.info',array('activity_id'=>intval($id),'fields'=>'limit_cat'));
        $lv1List = $activityData['limit_cat'];

        $catIds = implode(',',array_column($activityItem,'cat_id'));
        if($catIds)
        {
            $catLv3 = app::get('topc')->rpcCall('category.cat.get.info',array('cat_id'=>$catIds,'level'=>'3','fields'=>'cat_path,cat_name,cat_id'));
        }
        foreach($lv1List as $id=>$name)
        {
            $cat[$id]['cat_id']=  $id;
            $cat[$id]['cat_name']=  $name;
            if($catLv3)
            {
                foreach($catLv3 as $k=>$val)
                {
                    $catPath = explode(',',$val['cat_path']);
                    if($id == $catPath[1])
                    {
                        $cat[$id]['lv3'][] = $val;
                    }
                }
            }
        }
        return $cat;
    }

    public function saleRemind()
    {
        $uId = userAuth::id();
        $postdata = input::get('remind');
        $params = ['user_id'=>$uId,'activity_id' => $postdata['activity_id']];
        $remind = app::get('topc')->rpcCall('promotion.activity.remind.get',$params);
        $list = $remind['list'];
        if($remind['list'])
        {
            foreach($remind['list'] as $list)
            {
                if(isset($postdata['mobile']) && $list['remind_way'] == "mobile" && $list['remind_goal'] == $postdata['mobile'])
                {
                    return $this->splash('error', '', '该手机号已订阅该活动', true);
                }
                if (isset($postdata['email']) && $list['remind_way'] == "email" && $list['remind_goal'] == $postdata['email'])
                {
                    return $this->splash('error', '', '该邮箱已订阅该活动', true);
                }
            }
        }
        $postdata['user_id'] = $uId;
        $postdata['platform'] = 'topc';
        $postdata['url'] = url::action('topc_ctl_activity@activity_item_list', array('id'=>$postdata['activity_id']));

        try{
            $result = app::get('topc')->rpcCall('promotion.activity.remind.add',$postdata);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', '', $msg, true);
        }
        return $this->splash('success','','订阅成功',true);
    }

    public function toSaleRemind()
    {
        if( !userAuth::check())
        {
            $redirect_url = url::action('topc_ctl_passport@signin');
            return $this->splash('error',$redirect_url,'您没有登录',true);
        }
        $params = array(
            'activity_id' => input::get('activity_id'),
            'fields' => 'remind_time,activity_id,remind_way,remind_enabled,release_time,end_time,start_time',
        );
        $activitys = app::get('topc')->rpcCall('promotion.activity.list',$params);
        $pagedata['activity'] = $activitys['data'][0];
        if($pagedata['activity']['remind_way'] == "mobile")
        {
            $params = ['user_id'=>userAuth::id(),'remind_way'=>'mobile','time_field' =>'add_time','bthan'=>strtotime(date('Y-m-d')),'sthan'=>strtotime(date('Y-m-d 23.59.59'))];
            $remind = app::get('topc')->rpcCall('promotion.activity.remind.get',$params);
            $total = app::get('topc')->rpcCall('promotion.setting');
            //$pagedata['remind'] = $remind;
            $pagedata['remind_total'] = $total;
            $pagedata['remind_residue'] = $total['mobile_num']-$remind['count'];
        }
        //echo "<pre>";print_r($pagedata);exit;
        return view::make('topc/promotion/remind.html',$pagedata);
    }
}

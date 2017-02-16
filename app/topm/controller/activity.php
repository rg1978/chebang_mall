<?php
class topm_ctl_activity extends topm_controller{
    public $limit = 20;
    public function index()
    {
        $this->setLayoutFlag('activity_index');
        $post = input::get();
        $params = array(
            'start_time' => "sthan",
            'end_time' => "bthan",
            'order_by' => 'mainpush desc',
            'fields' => 'activity_name,activity_id,mainpush,slide_images,end_time,start_time,discount_max,discount_min',
        );
        $activitys = app::get('topm')->rpcCall('promotion.activity.list',$params);
        if(!$activitys['data'])
        {
            return $this->activity_list();
        }
        $pagedata['activity_list'] = $activitys['data'];
        $pagedata['now_time'] = time();

        return $this->page("topm/shop/promotion/activity.html",$pagedata);
    }

    public function activity_list()
    {
        $params = array(
            'release_time' => "sthan",
            'start_time' => "bthan",
            'order_by' => 'mainpush desc',
            'fields' => 'activity_name,activity_id,mainpush,slide_images,end_time,start_time,discount_max,discount_min',
        );
        $pagedata['title'] = "活动列表";
        $activitys = app::get('topm')->rpcCall('promotion.activity.list',$params);
        if(!isset($activitys['data']) || !$activitys['data'])
        {
            $this->setLayoutFlag('default');
            return $this->page("topm/shop/promotion/empty.html", $pagedata);
        }
        $pagedata['activity_list'] = $activitys['data'];
        //echo "<pre>";print_r($pagedata);exit;
        $pagedata['now_time'] = time();

        return $this->page("topm/shop/promotion/activitys.html",$pagedata);
    }

    public function activity_item_list()
    {
        $post = input::get();
        $post['id'] = $post['id'];

        $pagedata = $this->__getPagedata($post);
        if($activity = $pagedata['activity'])
        {
            if($activity['release_time'] > time())
            {
                return kernel::abort(404);
            }
        }
        $cativityData = app::get('topm')->rpcCall('promotion.activity.info',array('activity_id'=>$post['id'],'fields'=>'limit_cat'));
        $pagedata['catlist']  = $cativityData['limit_cat'];
        $pagedata['now_time'] = time();
        $pagedata['title'] = "活动商品列表";
        return $this->page("topm/shop/promotion/activity_item_list.html",$pagedata);
    }

    public function search()
    {
        $post = input::get();
        $pagedata = $this->__getPagedata($post);
        $cativityData = app::get('topm')->rpcCall('promotion.activity.info',array('activity_id'=>$post['id'],'fields'=>'limit_cat'));
        $pagedata['catlist']  = $cativityData['limit_cat'];
        return view::make('topm/shop/promotion/itemlist.html',$pagedata);
    }

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
        $groupItem = app::get('topm')->rpcCall('promotion.activity.item.info',$params);
        if($groupItem['activity_info']['release_time'] > time())
        {
            redirect::action('topm_ctl_item@index',array('item_id'=>$params['item_id']))->send();exit;
        }
        $pagedata['group_item'] = $groupItem;
        $pagedata['item'] = app::get('topm')->rpcCall('item.get',array('item_id'=>$params['item_id'],'fields'=>'item_count.sold_quantity,item_count.item_id'));
        $pagedata['shop'] = app::get('topm')->rpcCall('shop.get',array('shop_id'=>$pagedata['group_item']['shop_id'],'fields'=>'shop_name,shop_id'));
        $pagedata['now_time'] = time();
        $pagedata['shopDsrData'] = $this->__getShopDsr($pagedata['shop']['shop_id']);
        return $this->page("topm/shop/promotion/activity_detail.html",$pagedata);
    }

    private function __getShopDsr($shopId)
    {
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = true;
        $dsrData = app::get('topm')->rpcCall('rate.dsr.get', $params);
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



    public function itemlist()
    {
        $post = input::get();
        $pagedata = $this->__getPagedata($post);
        return view::make('topm/shop/promotion/list.html',$pagedata);
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
            'page_size' => intval($pageSize),
            'order_by' => $orderBy,
            'fields' => 'title,item_default_image,price,item_id,activity_id,sales_count,activity_price',
        );
        if($post['id'])
        {
            $params['activity_id'] = intval($post['id']);
        }

        if($post['cat_id'])
        {
            $params['cat_id'] = intval($post['cat_id']);
        }

        $pagedata['activity'] = app::get('topm')->rpcCall('promotion.activity.info',array('activity_id' => $params['activity_id'],'fields'=>'activity_id,activity_name,slide_images,activity_tag,start_time,end_time,release_time,discount_max,discount_min,remind_enabled'));;
        $item = app::get('topm')->rpcCall('promotion.activity.item.list',$params);
        $pagedata['group_item'] = $item['list'];
        $total = $item['count'];
        if( $total > 0 ) $totalPage = ceil($total/$this->limit);
        $pagedata['pagers'] = array(
            'link'=>url::action('topm_ctl_activity@itemlist',$post),
            'current'=>$page,
            'total'=>$totalPage,
        );
        $pagedata['now_time'] = time();
        return $pagedata;
    }

    public function ajaxItemShow()
    {
        $post = input::get();
        $pagedata = $this->__getPagedata($post);
        $data['html'] = view::make('topm/shop/promotion/list.html',$pagedata)->render();
        $data['pagers'] = $pagedata['pagers'];
        $data['success'] = true;
        return response::json($data);exit;
    }

    public function getCatLv3()
    {
        $id = input::get('catid');
        $catLv3 = app::get('topm')->rpcCall('category.cat.get.info',array('cat_path'=>intval($id),'level'=>'3','fields'=>'cat_name,cat_id'));

        $params['activity_id'] = intval(input::get('id'));
        $params['fields'] ='cat_id';
        $item = app::get('topm')->rpcCall('promotion.activity.item.list',$params);
        $catIds = array_column($item['list'],'cat_id');
        foreach($catIds as $id)
        {
            if($catLv3[$id])
            {
                $cat[] = $catLv3[$id];
            }
        }
        return  response::json($cat);exit;
    }

    public function saleRemind()
    {
        $uId = userAuth::id();
        $postdata = input::get('remind');
        $params = ['user_id'=>$uId,'activity_id' => $postdata['activity_id']];
        $remind = app::get('topm')->rpcCall('promotion.activity.remind.get',$params);
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
        $postdata['user_id'] =$uId ;

        try{
            $result = app::get('topm')->rpcCall('promotion.activity.remind.add',$postdata);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', '', $msg, true);
        }
        $post['id'] = $postdata['activity_id'];
        $redirect_url  = url::action('topm_ctl_activity@activity_item_list',$post);
        return $this->splash('success',$redirect_url,'订阅成功',true);
    }

    public function toRemind()
    {
        if( !userAuth::check() )
        {
            redirect::action('topm_ctl_passport@signin')->send();exit;
        }
        $params = array(
            'activity_id' => input::get('activity_id'),
            'fields' => 'remind_time,activity_id,remind_way,remind_enabled,release_time,end_time,start_time',
        );
        $activitys = app::get('topm')->rpcCall('promotion.activity.list',$params);
        $pagedata['activity'] = $activitys['data'][0];
        if($pagedata['activity']['remind_way'] == "mobile")
        {
            $params = ['user_id'=>userAuth::id(),'remind_way'=>'mobile','time_field' =>'add_time','bthan'=>strtotime(date('Y-m-d')),'sthan'=>strtotime(date('Y-m-d 23.59.59'))];
            $remind = app::get('topm')->rpcCall('promotion.activity.remind.get',$params);
            $total = app::get('topm')->rpcCall('promotion.setting');
            //$pagedata['remind'] = $remind;
            $pagedata['remind_total'] = $total;
            $pagedata['remind_residue'] = intval($total['mobile_num']-$remind['count']);
        }
        return $this->page("topm/shop/promotion/remind.html",$pagedata);
    }

}

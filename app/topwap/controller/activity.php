<?php
class topwap_ctl_activity extends topwap_controller{
    public $limit = 20;

    /**
     * 正在进行的活动
     * @return html
     */
    public function active_list()
    {
        $this->setLayoutFlag('activity_index');
        $post = input::get();
        $params = array(
            'start_time' => "sthan",
            'end_time' => "bthan",
            'order_by' => 'mainpush desc',
            'fields' => 'activity_name,activity_id,mainpush,slide_images,end_time,start_time,discount_max,discount_min',
        );
        $activitys = app::get('topwap')->rpcCall('promotion.activity.list',$params);
        // 没有正在进行中的活动则跳到即将开始活动页
        if(!$activitys['data'])
        {
             return redirect::action('topwap_ctl_activity@comming_list',['nodata'=>true]);
        }
        $pagedata['activity_list'] = $activitys['data'];

        return $this->page("topwap/activity/activity_active_list.html",$pagedata);
    }

    /**
     * 即将开始的活动
     * @return html
     */
    public function comming_list()
    {
        $pagedata['nodata'] = input::get('nodata');
        $params = array(
            'release_time' => "sthan",
            'start_time' => "bthan",
            'order_by' => 'mainpush desc',
            'fields' => 'activity_name,activity_id,mainpush,slide_images,end_time,start_time,discount_max,discount_min',
        );

        $activitys = app::get('topwap')->rpcCall('promotion.activity.list',$params);
        $pagedata['activity_list'] = $activitys['data'];

        return $this->page("topwap/activity/activity_comming_list.html",$pagedata);
    }

    public function detail()
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
        $cativityData = app::get('topwap')->rpcCall('promotion.activity.info',array('activity_id'=>$post['id'],'fields'=>'limit_cat'));
        $pagedata['catlist']  = $cativityData['limit_cat'];
        $pagedata['title'] = "活动商品列表";

        return $this->page("topwap/activity/activity_goods_list.html",$pagedata);
    }

    //店铺收藏的状态
    private function __CollectInfo($shopId)
    {
        $collect = unserialize($_COOKIE['collect']);
        if(in_array($shopId, $collect['shop']))
        {
            $pagedata['shopCollect'] = 1;
        }
        else
        {
            $pagedata['shopCollect'] = 0;
        }

        return $pagedata;
    }

    public function itemdetail()
    {
        if( userAuth::check() )
        {
            $pagedata['nologin'] = 1;
        }
        // $this->setLayoutFlag('product');
        $post = input::get();
        $params['fields'] = "*";
        $params['activity_id'] = $post['a'];
        $params['item_id'] = $post['g'];
        $groupItem = app::get('topwap')->rpcCall('promotion.activity.item.info',$params);
        if($groupItem['activity_info']['release_time'] > time())
        {
            redirect::action('topwap_ctl_item@index',array('item_id'=>$params['item_id']))->send();exit;
        }
        $pagedata['group_item'] = $groupItem;
        $pagedata['item'] = app::get('topwap')->rpcCall('item.get',array('item_id'=>$params['item_id'],'fields'=>'item_count.sold_quantity,item_count.item_id'));
        $pagedata['shop'] = app::get('topwap')->rpcCall('shop.get',array('shop_id'=>$pagedata['group_item']['shop_id'],'fields'=>'shop_name,shop_id,shop_logo'));
        //相册图片,相册轮播需要第一张和最后一张作为固定的图片，显示优化
        if( $pagedata['item']['images'] )
        {
            $pagedata['item']['first_image'] = reset($pagedata['item']['images']);
            $pagedata['item']['last_image'] = end($pagedata['item']['images']);
        }
        // 获取评价
        $pagedata['countRate'] = $this->__getRateResultCount($pagedata['item']);
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');

         //店铺收藏的状态
        $pagedata['collect'] = $this->__CollectInfo($pagedata['group_item']['shop_id']);
        
        return $this->page("topwap/activity/activity_goods_detail.html",$pagedata);
    }

    // ajax获取活动关联的商品列表
    public function itemlist()
    {
        $post = input::get();
        $pagedata = $this->__getPagedata($post);
        if( !$pagedata['pagers']['total'] )
        {
            return view::make('topwap/empty/item.html',$pagedata);
        }
        else
        {
            return view::make('topwap/activity/itemlist.html',$pagedata);
        }
    }

    private function __getPagedata($post)
    {
        $pagedata['filter'] = $post;
        $page = $post['pages'] ? $post['pages'] : 1;
        $pageSize = $this->limit;
        $orderBy = $post['order_by'];
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

        $pagedata['activity'] = app::get('topwap')->rpcCall('promotion.activity.info',array('activity_id' => $params['activity_id'],'fields'=>'activity_id,activity_name,slide_images,activity_tag,start_time,end_time,release_time,discount_max,discount_min,remind_enabled'));;
        $item = app::get('topwap')->rpcCall('promotion.activity.item.list',$params);
        $pagedata['group_item'] = $item['list'];
        $total = $item['count'];
        if( $total > 0 ) $totalPage = ceil($total/$this->limit);
        $pagedata['pagers'] = array(
            'link'=>url::action('topwap_ctl_activity@itemlist',$post),
            'current'=>$page,
            'total'=>$totalPage,
        );
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');
        return $pagedata;
    }

    // 保存订阅提醒
    public function saveRemind()
    {
        $uId = userAuth::id();
        $postdata = input::get('remind');
        $params = ['user_id'=>$uId,'activity_id' => $postdata['activity_id']];
        $remind = app::get('topwap')->rpcCall('promotion.activity.remind.get',$params);
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
        $postdata['user_id'] = $uId ;
        $postdata['platform'] = 'topwap';
        $postdata['url'] = url::action('topwap_ctl_activity@detail', array('id'=>$postdata['activity_id']));

        try{
            $result = app::get('topwap')->rpcCall('promotion.activity.remind.add',$postdata);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', '', $msg, true);
        }
        $post['id'] = $postdata['activity_id'];
        $redirect_url  = url::action('topwap_ctl_activity@detail', $post);
        return $this->splash('success',$redirect_url,'订阅成功',true);
    }

    // 订阅提醒页面
    public function remind()
    {
        if( !userAuth::check() )
        {
            return redirect::action('topwap_ctl_passport@goLogin')->send();
        }
        $params = array(
            'activity_id' => input::get('activity_id'),
            'fields' => 'remind_time,activity_id,remind_way,remind_enabled,release_time,end_time,start_time',
        );
        $activitys = app::get('topwap')->rpcCall('promotion.activity.list',$params);
        $pagedata['activity'] = $activitys['data'][0];
        if($pagedata['activity']['remind_way'] == "mobile")
        {
            $params = ['user_id'=>userAuth::id(),'remind_way'=>'mobile','time_field' =>'add_time','bthan'=>strtotime(date('Y-m-d')),'sthan'=>strtotime(date('Y-m-d 23.59.59'))];
            $remind = app::get('topwap')->rpcCall('promotion.activity.remind.get',$params);
            $total = app::get('topwap')->rpcCall('promotion.setting');
            //$pagedata['remind'] = $remind;
            $pagedata['remind_total'] = $total;
            $pagedata['remind_residue'] = intval($total['mobile_num']-$remind['count']);
        }
        return $this->page("topwap/activity/remind.html",$pagedata);
    }

    // 获取评论百分比
    private function __getRateResultCount($detailData)
    {
        if( !$detailData['rate_count'] )
        {
            $countRate['good']['num'] = 0;
            $countRate['good']['percentage'] = '0%';
            $countRate['neutral']['num'] = 0;
            $countRate['neutral']['percentage'] = '0%';
            $countRate['bad']['num'] = 0;
            $countRate['bad']['percentage'] = '0%';
            return $countRate;
        }
        $countRate['good']['num'] = $detailData['rate_good_count'];
        $countRate['good']['percentage'] = sprintf('%.2f',$detailData['rate_good_count']/$detailData['rate_count'])*100 .'%';
        $countRate['neutral']['num'] = $detailData['rate_neutral_count'];
        $countRate['neutral']['percentage'] = sprintf('%.2f',$detailData['rate_neutral_count']/$detailData['rate_count'])*100 .'%';
        $countRate['bad']['num'] = $detailData['rate_bad_count'];
        $countRate['bad']['percentage'] = sprintf('%.2f',$detailData['rate_bad_count']/$detailData['rate_count'])*100 .'%';
        $countRate['total'] = $detailData['rate_count'];
        return $countRate;
    }

}

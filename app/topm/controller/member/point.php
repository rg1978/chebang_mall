<?php
class topm_ctl_member_point extends topm_ctl_member{

    public function point()
    {

        $filter = input::get();
        $pagedata = $this->__getPoints($filter);
        if (!$pagedata) return redirect::back();
        $pagedata['title'] = "我的积分";
        return $this->page('topm/member/point/index.html',$pagedata);
    }


    public function __getPoints($filter)
    {
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = $this->limit;
        $current = ($filter['pages'] >=1 || $filter['pages'] <= 100) ? $filter['pages'] : 1;

        $params = array(
            'page_no' => intval($current),
            'page_size' => intval($pageSize),
            'orderBy' => 'modified_time desc',
            'user_id' => userAuth::id(),
        );
        $data = app::get('topm')->rpcCall('user.pointGet',$params);

        //总页数(数据总数除每页数量)
        $pagedata['userpoint'] = $data['datalist']['user'];
        $pagedata['pointdata'] = $data['datalist']['point'];
        if($data['totalnum'] > 0) $total = ceil($data['totalnum']/$pageSize);

        $pagedata['count'] = $data['totalnum'];
        $filter['pages'] = time();
        $pagedata['pagers'] = array(
            'link'=>url::action('topm_ctl_member_point@point',$filter),
            'current'=>$current,
            'total'=>$total,
        );
        return $pagedata;
    }

    public function ajaxPointShow()
    {
        $filter = input::get();
        $pagedata = $this->__getPoints($filter);
        if ( !$pagedata )
        {
            $data['error'] =ture;
            return response::json($data);exit;
        }
        $data['html'] = view::make('topm/member/point/points.html',$pagedata)->render();
        $data['pagers'] = $pagedata['pagers'];
        $data['success'] = true;
        return response::json($data);exit;
    }


    public function ajaxGetUserPoint()
    {
        $totalPrice = input::get('total_price');
        $totalPostFee = input::get('post_fee');
        $totalPrice = $totalPrice-$totalPostFee;
        $userId = userAuth::id();
        //根据会员id获取积分总值
        $points = app::get('topm')->rpcCall('user.point.get',['user_id'=>$userId]);
        $setting = app::get('topm')->rpcCall('point.setting.get');
        $pagedata['open_point_deduction'] = $setting['open.point.deduction'];
        $pagedata['point_deduction_rate'] = $setting['point.deduction.rate'];
        $pagedata['point_deduction_max'] = floor($setting['point.deduction.max']*$totalPrice*$setting['point.deduction.rate']);
        $pagedata['points'] = $points['point_count'] ? $points['point_count'] : 0;
        //print_r($pagedata);exit;
        return response::json($pagedata);exit;
    }
}



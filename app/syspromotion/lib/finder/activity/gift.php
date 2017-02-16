<?php
class syspromotion_finder_activity_gift{

    public $column_edit = "操作";
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_edit(&$col ,$list)
    {
        foreach($list as $k=>$row)
        {
            if(app::get('sysconf')->getConf('shop.promotion.examine')){
                if ($row['gift_status'] =='pending') {
                    $title = app::get('syspromotion')->_('审核');
                    $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'index','ctl'=>'admin_gift','finder_id'=>$_GET['_finder']['finder_id'],'id'=>$row['gift_id'],'finderview'=>'detail_basic','action'=>'detail','singlepage'=>'true']);
                    $col[$k] = '<a href="'.$url.'" target="_blank" title="审核">审核</a>';
                }
            }
        }   
    }
    
    public $detail_basic = '促销信息';
    public function detail_basic($id)
    {
      //  $giftData = app::get('syspromotion')->model('gift')->getRow('*', array('gift_id'=>$id));

        // 获取活动规则信息
        $apiData = array(
            'gift_id' => $id,
            'gift_itemList' => true,
        );
        $pagedata = app::get('syspromotion')->rpcCall('promotion.gift.get', $apiData);
        
        $valid_grade = explode(',', $pagedata['valid_grade']);
        $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');
        $gradeIds = array_column($pagedata['gradeList'],'grade_id');
        if( !array_diff($gradeIds, $valid_grade))
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
            $gradeStr = rtrim($gradeStr,',');
        }
        $pagedata['grade_str'] = $gradeStr;
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.promotion.examine');

        $examineData= redis::scene('syspromotion')->lrange('gift_id_'.$id,0,-1);
        $pagedata['examineLog'] = array();
        foreach ($examineData as $key => $value) {
            $pagedata['examineLog'][$key] = unserialize($value);
        }
//echo"<pre>";print_r($pagedata);exit();
        return view::make('syspromotion/activity/gift/detail.html',$pagedata)->render();
    }
}

<?php
class syspromotion_finder_activity_xydiscount{

    public $column_rule = "促销规则";
    public $column_rule_order = 1;
    public $column_rule_width = 300;

    public $column_edit = "操作";
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_rule(&$colList,$list)
    {
        $registerData = app::get('syspromotion')->model('activity');
        foreach($list as $k=>$row)
        {
            if($row['condition_value'])
            {
                $data = explode(',',$row['condition_value']);
                foreach($data as $val)
                {
                    $value = explode('|',$val);
                    $colList[$k] .= '满'.$value[0].'件,给予'.$value['1'].'%的折扣'."；";
                }
            }
        }
    }

    public function column_edit(&$col ,$list)
    {
        foreach($list as $k=>$row)
        {
            if(app::get('sysconf')->getConf('shop.promotion.examine')){
                if ($row['xydiscount_status'] =='pending') {
                    $title = app::get('syspromotion')->_('审核');
                    $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'index','ctl'=>'admin_xydiscount','finder_id'=>$_GET['_finder']['finder_id'],'id'=>$row['xydiscount_id'],'finderview'=>'detail_basic','action'=>'detail','singlepage'=>'true']);
                    $col[$k] = '<a href="'.$url.'" target="_blank" title="审核">审核</a>';
                }
            }
        }   
    }

    public $detail_basic = '促销信息';
    public function detail_basic($id)
    {
        $xydiscountData = app::get('syspromotion')->model('xydiscount')->getRow('*', array('xydiscount_id'=>$id));
        // 获取活动规则信息
        $apiData = array(
            'xydiscount_id' => $xydiscountData['xydiscount_id'],
            'xydiscount_itemList' => true,
        );
        $pagedata = app::get('syspromotion')->rpcCall('promotion.xydiscount.get', $apiData);
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
        $pagedata['condition_value'] = $this->condition($pagedata['condition_value']);
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.promotion.examine');

        $examineData= redis::scene('syspromotion')->lrange('xydiscount_id_'.$id,0,-1);
        $pagedata['examineLog'] = array();
        foreach ($examineData as $key => $value) {
            $pagedata['examineLog'][$key] = unserialize($value);
        }
        
        return view::make('syspromotion/activity/xydiscount/detail.html',$pagedata)->render();
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
}

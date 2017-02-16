<?php
class topm_ctl_member_experience extends topm_ctl_member{

    public function experience()
    {
        $filter = input::get();
        $pagedata = $this->__getExperience($filter);
        return $this->page('topm/member/experience/index.html',$pagedata);
    }

    private function __getExperience($filter)
    {
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }

        $params = array(
            'page_no' => intval($filter['pages']),
            'page_size' => intval($this->limit),
            'orderBy' => 'modified_time desc'
        );

        $pagedata['grade'] = app::get('topm')->rpcCall('user.grade.fullinfo', [], 'buyer');

        $data = app::get('topm')->rpcCall('user.experienceGet',$params);
        //总页数(数据总数除每页数量)
        $pagedata['userexp'] = $data['datalist']['user'];
        $pagedata['experiencedata'] = $data['datalist']['exp'];
        if($data['totalnum'] > 0) $total = ceil($data['totalnum']/$this->limit);
        $pagedata['count'] = $data['totalnum'];
        $current = intval($filter['pages']) ? intval($filter['pages']) : 1;
        $filter['pages'] = time();
        $pagedata['pagers'] = array(
            'link'=>url::action('topm_ctl_member_experience@ajaxExperienceShow',$filter),
            'current'=>$current,
            'total'=>$total,
        );
        $pagedata['title'] = "我的成长值";
        return $pagedata;
    }

    public function ajaxExperienceShow()
    {
        $filter = input::get();
        $pagedata = $this->__getExperience($filter);
        if ( !$pagedata )
        {
            $data['error'] =ture;
            return response::json($data);exit;
        }
        $data['html'] = view::make('topm/member/experience/experience.html',$pagedata)->render();
        $data['pagers'] = $pagedata['pagers'];
        $data['success'] = ture;
        return response::json($data);exit;
    }



    public function grade()
    {

        $grade = app::get('topm')->rpcCall('user.grade.fullinfo','','buyer');
        foreach($grade['gradeList'] as $key=>$val)
        {
            $pagedata['grade'][] = array(
                'name' => $key+1,
                'descritpion' => $val['grade_name'],
                'number' => $val['experience'],
            );
        }
        $pagedata['count'] = count($grade['gradeList']);
        $pagedata['title'] = "成长值体系";
        return $this->page('topm/member/experience/grade.html',$pagedata);
    }
}

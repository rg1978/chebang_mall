<?php
class syspromotion_tasks_remind extends base_task_abstract implements base_interface_task{

    var $limit = 100;
    public function exec($params=null)
    {
        $filter = array(
            'remind_time|sthan' => time(),
            'remind_status' => 0,
        );
        $objMdlRemind = app::get('syspromotion')->model('remind');
        $remindList = $objMdlRemind->getList('*',$filter);
        $activiry = array();
        $emailRemind = array();
        $mobileRemind = array();

        foreach($remindList as $key=>$value)
        {
            if($value['remind_way'] == "email")
            {
                $emailRemind[$value['activity_id']][] = $value;
            }
            elseif($value['remind_way'] == "mobile")
            {
                $mobileRemind[$value['activity_id']][] = $value;
            }
        }
        //return true;

        $tmpl = "activity-remind";
        $result = true;
        //处理邮件
        if($emailRemind)
        {
            foreach($emailRemind as $key=>$val)
            {
                foreach($val as $v)
                {
                    $id = $v['remind_id'];
                    $email['email'] = $v['remind_goal'];
                    $data['item_name'] = $v['item_name'];
                    $data['time'] = date('H:i',$v['start_time']);
                    $data['site_name'] = app::get('site')->getConf('site.name');
                    $data['url'] = $v['url'];
                    $result = messenger::send($email,$tmpl,$data);
                    if($result)
                    {
                        $objMdlRemind->update(['remind_status'=>1],['remind_id'=>$id]);
                    }
                }
            }
        }

        //处理短信
        if($mobileRemind)
        {
            foreach($mobileRemind as $key=>$val)
            {
                $id = array();
                foreach($val as $v)
                {
                    $id[] = $v['remind_id'];
                    $mobile['sms'][] = $v['remind_goal'];
                    $data['time'] = date('H:i',$v['start_time']);
                }
                $result = messenger::send($mobile,$tmpl,$data);
                if($result)
                {
                    $objMdlRemind->update(['remind_status'=>1],['remind_id'=>$id]);
                }
            }
        }

        return true;
    }
}

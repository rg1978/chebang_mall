<?php
class syspromotion_api_remind_addRemind{

    public $apiDescription = '新增提醒';
    public function getParams()
    {
        $return['params'] = array(
            'user_id'       => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'设置提醒的会员id'],
            'activity_id'   => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'设置提醒的活动id'],
            'item_id'       => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'设置提醒的商品id'],
            'email'         => ['type'=>'string', 'valid'=>'email', 'default'=>'', 'example'=>'', 'description'=>'邮件账号'],
            'mobile'        => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'电话号码'],
            'platform'      => ['type'=>'string', 'valid'=>'in:topc,topwap,topm', 'default'=>'', 'example'=>'', 'description'=>'订阅平台'],
            'url'           => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'订阅链接'],
        );
        return $return;
    }
    public function remindAdd($params)
    {
        if(!$params['email'] && !$params['mobile'])
        {
            throw new \LogicException(app::get('syspromotion')->_('邮件和短信至少有一项必填'));
        }

        $objMdlActivity = app::get('syspromotion')->model('activity');
        $objMdlActivityItem = app::get('syspromotion')->model('activity_item');
        if($params['activity_id'])
        {
            $data = $objMdlActivity->getList('activity_name,start_time,remind_enabled,remind_way,remind_time',array('activity_id'=>$params['activity_id']));
            if(!$data)
            {
                throw new \LogicException(app::get('syspromotion')->_('订阅的活动不存在'));
            }
            $data = $data[0];

            if($params['item_id']){
                $item = $objMdlActivityItem->getList('title',array('item_id'=>$params['item_id'],'activity_id'=>$params['activity_id']));
                if(!$item)
                {
                    throw new \LogicException(app::get('syspromotion')->_('订阅的活动中的商品不存在'));
                }
                $item = $item[0];
            }
            $remindTime = $data['start_time']-$data['remind_time']*60;

            $addData = array(
                'user_id' => $params['user_id'],
                'item_id' => $params['item_id'],
                'activity_id' => $params['activity_id'],
                'remind_time' => $remindTime,
                'item_name' => $item['title'],
                'start_time' => $data['start_time'],
                'activity_name' => $data['activity_name'],
                'platform' => $params['platform'],
                'url' => $params['url'],
                'add_time' => time(),
            );

            if($params['email'])
            {
                $addData['remind_way'] = "email";
                $addData['remind_goal'] = $params['email'];
                $result = kernel::single('syspromotion_remind')->doAdd($addData);
            }

            if($params['mobile'])
            {
                $addData['remind_way'] = "mobile";
                $addData['remind_goal'] = $params['mobile'];
                $result = kernel::single('syspromotion_remind')->doAdd($addData);
            }
        }
        if(!$result)
        {
            throw new \LogicException(app::get('syspromotion')->_('订阅失败'));
        }
        return true;
    }
}

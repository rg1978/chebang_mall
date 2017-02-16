<?php

class sysuser_events_listeners_sendPoint {

    //注册送积分
    public function sendPoint($userId)
    {
        // 注册送积分
        $setting = app::get('sysconf')->getConf('open.sendPoint');
        if($setting)
	{
            $sendPointNum = app::get('sysconf')->getConf('sendPoint.num');
            $updateParams = array(
                'user_id' => $userId,
                'type' => 'obtain',
                'num' => $sendPointNum,
                'behavior' => '注册送积分',
                'remark' => '注册送积分',
            );
        $result = app::get('topc')->rpcCall('user.updateUserPoint',$updateParams);
	}

        return true;
    }

}

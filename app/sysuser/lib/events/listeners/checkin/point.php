<?php
/**
 *会员签到送积分
 */
class sysuser_events_listeners_checkin_point
{
	public function updateUserInfo($userId){

		$settiing = app::get('sysconf')->getConf('open.point');
		$sendPointNum = app::get('sysconf')->getConf('checkinPoint.num');

		if ($settiing){
			//更新会员积分
			$updateParams = array(
            	'user_id' => $userId,
            	'type' => 'obtain',
            	'num' => $sendPointNum,
            	'behavior' => '签到送积分',
            	'remark' => '签到送积分',
        	);
        	$result = app::get('topc')->rpcCall('user.updateUserPoint',$updateParams);

			return $result;
		}
	}
}

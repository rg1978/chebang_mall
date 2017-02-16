<?php
class sysuser_data_user_points{


    /**
     * 处理会员过期积分
     *
     * @param int $userId
     * @return bool
     */
    public function pointExpiredCount($userId=null)
    {
        $expiredMonth = app::get('sysconf')->getConf('point.expired.month');
        $expiredMonth = $expiredMonth ? $expiredMonth : 12;
        $expiredTime = strtotime(date('Y-'.$expiredMonth.'-01 23:59:59')."-1 year +1 month -1 day");

        //error_log(date('Y-m-d H:i:s',$expiredTime)."------\n",3,DATA_DIR."/bbb.log");

        if(time() >= $expiredTime)
        {
            $objMdlUserPoints = app::get('sysuser')->model('user_points');
            $objMdlUserPointlog = app::get('sysuser')->model('user_pointlog');

            $userPoints = $objMdlUserPoints->getRow('user_id,point_count,expired_point,expired_time',array('user_id'=>$userId));
            //error_log(date('Y-m-d H:i:s',$userPoints['expired_time'])."------\n",3,DATA_DIR."/bbb.log");

            if(isset($userPoints['expired_time']) && $userPoints['expired_time'] && $userPoints['expired_time'] != $expiredTime)
            {
                return true;
            }

            if($userPoints['expired_point'] <= 0)
            {
                return true;
            }

            $newExpiredTime = strtotime(date('Y-'.$expiredMonth.'-01 23:59:59')." +1 month -1 day");
            $userPoints['expired_point'] = $userPoints['point_count'] = $userPoints['point_count']-$userPoints['expired_point'];
            $userPoints['modified_time'] = time();
            $userPoints['expired_time'] = $newExpiredTime;
            $db = app::get('sysuser')->database();
            $db->beginTransaction();
            try
            {
                $result = $objMdlUserPoints->save($userPoints);
                $result = $objMdlUserPointlog->delete(array('user_id'=>$userId,'modified_time|sthan'=>$expiredTime));
            }
            catch(\LogicException $e)
            {
                $db->rollback();
                $msg = $e->getMessage();
                logger::info('point_expired:'.$msg);
                return false;
            }
            $db->commit();
            return true;
        }
        return true;
    }

    /**
     * @brief 积分改变
     *
     * @param $params
     *
     * @return
     */
    public function changePoint($params)
    {
        if(!$params['user_id'])
        {
            throw new Exception('会员参数错误');
        }
        if(!$params['modify_point'])
        {
            throw new Exception('会员积分参数错误');
        }

        $db = app::get('sysuser')->database();
        $db->beginTransaction();
        try{
            $data['user_id'] = $params['user_id'];
            $data['remark'] = $params['modify_remark'] ? $params['modify_remark'] : "平台修改";
            $data['point'] = abs($params['modify_point']);
            $data['modified_time'] = time();
            if($params['modify_point'] >= 0)
            {
                $data['behavior_type'] = "obtain";
                $data['behavior'] = $params['behavior'] ? $params['behavior'] : "平台手动增加积分";
                $result = $this->add($data['user_id'],$data['point']);
            }
            elseif($params['modify_point'] < 0)
            {
                $data['behavior_type'] = "consume";
                $data['behavior'] = $params['behavior'] ? $params['behavior'] : "平台手动扣减积分";
                $result = $this->deduct($data['user_id'],$data['point']);
            }
            if(!$result)
            {
                throw new Exception('会员积分值更改失败');
            }
            $objMdlUserPointsLog = app::get('sysuser')->model('user_pointlog');
            $result = $objMdlUserPointsLog->save($data);
            if(!$result)
            {
                throw new Exception('会员积分值明细记录失败');
            }
            $db->commit();
            return true;
        }catch(\LogicException $e){
            $db->rollback();
            throw new Exception($e->getMessage());
            return false;
        }
    }

    /**
     * @brief 积分增加
     *
     * @param $userId
     * @param $data
     *
     * @return
     */
    public function add($userId,$data)
    {
        $db = app::get('sysuser')->database();
        $list = $db->executeQuery('SELECT user_id FROM sysuser_user_points WHERE user_id=?',[$userId])->fetch();
        if($list)
        {
            $result = $db->executeUpdate('UPDATE sysuser_user_points SET point_count = point_count + ? WHERE user_id = ?', [$data, $userId]);
        }
        else
        {
            $result = $db->executeUpdate('insert into sysuser_user_points(user_id,point_count) value (?,?)',[$userId,$data]);
        }
        if(!$result)
        {
            return false;
        }
        return true;
    }

    /**
     * @brief 积分消耗
     *
     * @param $userId
     * @param $data
     *
     * @return
     */
    public function deduct($userId,$data)
    {
        $db = app::get('sysuser')->database();
        $list = $db->executeQuery('SELECT user_id,expired_point,point_count FROM sysuser_user_points WHERE user_id=?',[$userId])->fetch();
        if($list)
        {
            if($list['expired_point'] > 0)
            {
                $expired = ($list['expired_point'] < $data) ? $list['expired_point'] : $data;
                $result = $db->executeUpdate('UPDATE sysuser_user_points SET expired_point = expired_point - ?,point_count = point_count - ? WHERE user_id = ? AND expired_point - ? >= 0', [$expired,$data, $userId, $expired]);
            }
            else
            {
                $result = $db->executeUpdate('UPDATE sysuser_user_points SET point_count = point_count - ? WHERE user_id = ? ', [$data, $userId]);
            }

            if(!$result)
            {
                return false;
            }
            return true;
        }
        else
        {
            throw new Exception('该用户没有积分，不能减积分');
        }
    }
}

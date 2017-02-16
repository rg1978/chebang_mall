<?php

class sysuser_check{

    public function checkDelete($userIds)
    {
        $uIds = $userIds;
        if(is_array($userIds))
        {
            $uIds = implode(',',$userIds);
        }
        $filter['user_id'] = $uIds;
        $filter['status'] = 'WAIT_BUYER_PAY,WAIT_SELLER_SEND_GOODS,WAIT_BUYER_CONFIRM_GOODS';
        $filter['fields'] = 'tid,user_id';
        $tradeCheck = app::get('sysuser')->rpcCall('trade.get.list',$filter);
        if($tradeCheck['count'] > 0)
        {
			throw new \LogicException(app::get('sysuser')->_('该会员有订单未处理'));
            return false;
        }

        $afterSalesFilter['user_id'] = $uIds;
        $afterSalesFilter['progress'] = '0,1,2,5,8';
        $afterSalesFilter['fields'] = 'aftersales_bn,user_id';
        $afterSalesCheck = app::get('sysuser')->rpcCall('aftersales.list.get',$afterSalesFilter);
        if($afterSalesCheck['total_found'] > 0)
        {
			throw new \LogicException(app::get('sysuser')->_('该会员有售后未处理'));
            return false;
        }

        $pointCheck = $this->pointCheck(array('user_id'=>$userIds));
        if($pointCheck)
        {
			throw new \LogicException(app::get('sysuser')->_('该会员有未使用的积分'));
            return false;
        }

        //验证是否有预存款，有预存款的用户不能删除
        if($this->depositCheck(array('user_id'=>$userIds)))
        {
            throw new \LogicException(app::get('sysuser')->_('该会员有未使用的预存款'));
            return false;
        }

        return true;
    }

    public function depositCheck($filter)
    {
        $noDel = array();
        $objMdlPoints = app::get('sysuser')->model('user_deposit');
        $deposits = $objMdlPoints->getList('deposit,user_id',$filter);
        if($deposits)
        {
            foreach($deposits as $key=>$val)
            {
                if($val['deposit'] > 0)
                {
                    $noDel[] = $val['user_id'];
                }
            }
        }
        return $noDel;
    }

    public function pointCheck($filter)
    {
        $noDel = array();
        $objMdlPoints = app::get('sysuser')->model('user_points');
        $points = $objMdlPoints->getList('point_count,user_id',$filter);
        if($points)
        {
            foreach($points as $key=>$val)
            {
                if($val['point_count'] > 0)
                {
                    $noDel[] = $val['user_id'];
                }
            }
        }
        return $noDel;
    }
}

<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_userListData
{
     //给经营概况统计数据
    public function getUserOperatData($data)
    {
        if($data['timeType'])
        {
            $timeRange = kernel::single('sysstat_desktop_commonData')->getTimeRangeByType($data['timeType']);
            //$timeRange = $this->_getTimeRangeByType($data['timeType']);
            $timeStart = strtotime($timeRange['time_start']);
            $timeEnd = strtotime($timeRange['time_end']);
        }
        else
        {
            $timeStart = strtotime($data['time_start']);
            $timeEnd = strtotime($data['time_end']);
        }
        $mdlDesktopStatUser = app::get('sysstat')->model('desktop_stat_user');
        $filter = array(
            'createtime|bthan'=>$timeStart,
            'createtime|lthan'=>$timeEnd
        );

        $fileds = 'newuser,accountuser,sellernum,selleraccount';
        $statUserData = $mdlDesktopStatUser->getList($fileds,$filter,0,-1,'createtime ASC');
        foreach ($statUserData as $key => $value)
        {
            foreach ($value as $k => $v)
            {
                $operatData[$k] += $v;
            }
            $operatData['accountuser'] = $value['accountuser'];
            $operatData['selleraccount'] = $value['selleraccount'];
        }
        //echo '<pre>';print_r($operatData);exit();
        return $operatData;
    }
     /**
     * 获取公共数据
     * data  页面传过来的数据
     * @return array
     */
    public function getCommonData($data)
    {
        if(strtotime($data['time_start'])>strtotime($data['time_end']))
        {
            throw new \LogicException(app::get('sysstat')->_("开始时间必须小于结束时间"));
        }

        if($data['timeType'])
        {
            $timeRange = kernel::single('sysstat_desktop_commonData')->getTimeRangeByType($data['timeType']);
            $timeStart = strtotime($timeRange['time_start']);
            $timeEnd = strtotime($timeRange['time_end']);
        }
        else
        {
            $timeStart = strtotime($data['time_start']);
            $timeEnd = strtotime($data['time_end']);
        }

        $dataType = $data['dataType']?$data['dataType']:'num';
        $limit = $data['userLimit']?$data['userLimit']:5;
        //获取店铺排行数据
        $userListInfo = $this->_getUserListData($dataType,$timeStart,$timeEnd,$limit);

       //echo '<pre>';print_r($tradeData);exit();
        $pagedata['userListData'] = $userListInfo;
        $pagedata['time_start'] = date('Y/m/d',$timeStart);
        $pagedata['time_end'] = date('Y/m/d',$timeEnd);
        //echo '<pre>';print_r($pagedata);exit();
        return $pagedata;
    }


    /**
     * @brief  获取交易数据
     * $dataType 数据类型  是件数num,还是钱money,string
     * $timeStart 查询的开始时间 2015-03-01
     * $timeEnd 查询的结束时间2015-03-03
     * 
     * @return array
     */
    private function _getUserListData($dataType,$timeStart,$timeEnd,$limit)
    {
        $mdlDesktopUserStat = app::get('sysstat')->model('desktop_stat_userorder');
        if($dataType=='num')
        {
            $orderBy = 'userordernum';
        }
        if($dataType=='money')
        {
            $orderBy = 'userfee';
        }
        $filter = array(
            'timeStart'=>$timeStart,
            'timeEnd'=>$timeEnd+1
        );
        if(!$limit)
        {
            $limit = -1;
        }
        $fileds = 'user_id,username,userordernum,userfee,experience,createtime';
        //echo '<pre>';print_r($orderBy);exit();
        $userListData = $mdlDesktopUserStat->getUserList($fileds,$filter,0,$limit,$orderBy);
    
        return $userListData;
    }
}

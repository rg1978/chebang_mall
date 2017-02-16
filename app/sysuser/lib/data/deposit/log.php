<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_deposit_log
{
    /**
     * 记录预存款日志
     *
     * @param userId int 用户id
     * @param operator string 操作员
     * @param fee money 金额
     * @param msg string 日志内容
     *
     * @return bool 记录是否成功，如果不成功会抛出异常，所以不需要返回false
     */
    public function addLog($userId, $operator, $fee, $msg, $type='expense')
    {

        $sdf = [
            'user_id' => $userId,
            'type' => $type == 'expense' ? 'expense' : 'add',
            'fee' => $fee,
            'operator' => $operator,
            'logtime' => time(),
            'message' => $msg
            ];
        $objDepositLog = app::get('sysuser')->model('user_deposit_log');
        $objDepositLog->insert($sdf);

        return true;
    }

    /**
     *
     * 获取日志记录列表
     *
     * @param userId int 用户id
     * @param page int 分页的页数
     * @param rowNum int 每页的行数
     *
     */
    public function get($userId, $page = 1, $rowNum = 10)
    {
        $objDepositLog = app::get('sysuser')->model('user_deposit_log');
        $count = $objDepositLog->count(['user_id'=>$userId]);

        $rowNum = $rowNum > 10 ? 10 : $rowNum;
        $rowNum = $rowNum < 1  ?  1 : $rowNum;
        $pageTotal = ceil($count/$rowNum);
        $page = ($pageTotal < $page) ? $pageTotal : $page;
        $offset = ($page - 1) * $rowNum;

        $logs = $objDepositLog->getList('type,operator,fee,message,logtime', ['user_id'=>$userId], $offset, $rowNum, 'logtime DESC');

        return [$logs, $count];
    }
    /**
     *
     * @brief 获取日志记录列表
     *
     * @param userId int 用户id
     *
     */
    public function getAll($userId)
    {
        $objDepositLog = app::get('sysuser')->model('user_deposit_log');
        $logs = $objDepositLog->getList('type,operator,fee,message,logtime', ['user_id'=>$userId], null,null,'logtime DESC');

        return $logs;
    }
}


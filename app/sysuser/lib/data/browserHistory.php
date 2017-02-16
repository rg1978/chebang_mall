<?php
/**
 * 消费者浏览商品历史纪录
 */
class sysuser_data_browserHistory {

    /**
     * 消费者浏览商品历史最多显示多少个商品
     */
    public $limit = 5;

    /**
     * 存储商品浏览历史纪录
     *
     * @param $userId string 用户ID
     * @param $itemIds int|array 浏览的商品ID
     */
    public function store($userId, $itemIds)
    {
        foreach( $itemIds as $key=>$itemId )
        {
            //去除空值 和 非数字的值
            if( !$itemId || !is_numeric($itemId) )
            {
                unset($itemIds[$key]);
            }
        }
        if( empty($itemIds) ) return true;

        $payLoadId = $this->__createPayload($userId);

        $historyItemIds = $this->getUserBrowserHistory($userId);
        if( $historyItemIds )
        {
            $itemIds = array_merge($historyItemIds, (array)$itemIds);
        }

        //反转，将最新的放到最左边
        $itemIds = array_reverse($itemIds);
        //去重
        $itemIds = array_unique($itemIds);
        //只取规定长度的数据
        $itemIds = array_chunk($itemIds, $this->limit)[0];

        //清空已有数据
        redis::scene('browserHistory')->ltrim($payLoadId, -1, 0);
        redis::scene('browserHistory')->lpop($payLoadId);

        return redis::scene('browserHistory')->lpush($payLoadId, $itemIds);
    }

    /**
     * 获取指定用户的商品浏览历史
     *
     * @param $userId int 用户ID
     */
    public function getUserBrowserHistory($userId)
    {
        $limit = $this->getLimit();
        return redis::scene('browserHistory')->lrange($this->__createPayload($userId), 0, $limit);
    }

    /**
     * 获取用户浏览商品历史队列长度
     */
    public function getLimit()
    {
        return $this->limit-1;
    }

    /**
     * 创建存储商品浏览历史KEY
     */
    private function __createPayload($userId)
    {
        return md5('browserItemsHistory'.$userId);
    }
}


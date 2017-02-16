<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */


class sysuser_data_deposit_cashLocker
{
   /**
     * 支持场景类型
     *
     * @var array
     */
    static private $supportScenes = ['deposit.cash.limit'];

   /**
     * instances
     *
     * @var array
     */
    static private $instances = [];

   /**
     * scene
     *
     * @var misc
     */
    public $scene = null;

    /**
     * 获取小时ttl
     *
     * @return int
     */
    public function getHourTtl()
    {
        return 24;
    }

    /**
     * 获取分钟ttl
     *
     * @return int
     */
    public function getMinuteTtl()
    {
        return $this->getHourTtl() * 60;
    }

    /**
     * 获取限制次数
     *
     * @return int
     */
    public function getLimitTimes()
    {
        return (int)app::get('sysconf')->getConf("user.deposit.cash.limit.times");
    }

    /**
     * 生成对应的cache key
     *
     * @return string
     */
    public function prepareKey($userId)
    {
        return 'deposit_cash_limit_times_' . $this->scene . '_' . $userId . '_' . (int)(time() / (24 * 60 * 60));
    }

    /**
     * 获取剩余次数
     *
     * @return null
     */
    public function getTimesLeft($userId)
    {
        $times = cache::store('sysuser')->get($this->prepareKey($userId));
        return $times === null ? $this->getLimitTimes($userId) : $times;
    }

    /**
     * 检查是否已经锁定
     *
     * @return null
     */
    public function checkLock($userId)
    {
        if (cache::store('sysuser')->get($this->prepareKey($userId)) === 0) {
            throw new \LogicException(app::get('sysuser')->_("今天提现次数已经用完"));
        }
    }

    /**
     * 提现记录
     *
     * @return null
     */
    public function runCash($userId)
    {
        cache::store('sysuser')->decrement($this->prepareKey($userId),
                                           1,
                                           $this->getLimitTimes(),
                                           $this->getMinuteTtl());
    }

    /**
     * 清除锁
     *
     * @return null
     */
    public function clean($userId) {
        cache::store('sysuser')->forget($this->prepareKey($userId));
    }
}

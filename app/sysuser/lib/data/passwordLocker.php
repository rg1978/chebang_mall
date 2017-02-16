<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */


class sysuser_data_passwordLocker
{
   /**
     * 支持场景类型
     *
     * @var array
     */
    static private $supportScenes = ['deposit.password'];

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
     * 获得对应场景的实例
     *
     * @param  string  $scene
     * @return sysuser_data_passwordLocker
     */
    static function instance($scene)
    {
        if (static::$instances[$scene]) return static::$instances[$scene];

        if (!in_array($scene, static::$supportScenes)) {
            throw new InvalidArgumentException(sprintf('sysuser_data_passwordLocker not support scene:%s', $scene));
        }
        return new sysuser_data_passwordLocker($scene);
    }

    /**
     * create a new scene instance
     *
     * @param  string  $scene
     * @return null
     */
    public function __construct($scene)
    {
        $this->scene = $scene;
    }

    /**
     * 获取小时ttl
     *
     * @return int
     */
    public function getHourTtl()
    {
        return (int)app::get('sysconf')->getConf("user.{$this->scene}.hour.ttl");
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
     * 获取重试次数
     *
     * @return int
     */
    public function getRetryTimes()
    {
        return (int)app::get('sysconf')->getConf("user.{$this->scene}.retry.times");
    }

    /**
     * 获取需要提示的重试次数
     *
     * @return int
     */
    public function getRemindRetryTimes()
    {
        return app::get('sysconf')->getConf("user.{$this->scene}.remind.retry.times");

    }

    /**
     * 生成对应的cache key
     *
     * @return string
     */
    protected function prepareKey($userId)
    {
        return 'password-lock_' . $this->scene . '_' . $userId;
    }

    /**
     * 检查是否已经锁定
     *
     * @return null
     */
    public function checkLock($userId)
    {
        if (cache::store('sysuser')->get($this->prepareKey($userId)) === 0) {
            throw new \LogicException(app::get('sysuser')->_("密码错误{$this->getRetryTimes()}次，您可以找回密码，或{$this->getHourTtl()}小时后再试。"));
        }
    }

    /**
     * 尝试重试
     *
     * @return null
     */
    public function tryVerify($userId)
    {
        $retryTimes = $this->getRetryTimes();
        $remindRetryTimes = $this->getRemindRetryTimes();
        $residualRetryTimes = cache::store('sysuser')->decrement($this->prepareKey($userId),
                                                                 1,
                                                                 $this->getRetryTimes(),
                                                                 $this->getMinuteTtl());

        $this->checkLock($userId);

        if ($residualRetryTimes <= $remindRetryTimes) {
            throw new \LogicException(app::get('sysuser')->_("密码错误，您还可以尝试{$residualRetryTimes}次"));
        }
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
